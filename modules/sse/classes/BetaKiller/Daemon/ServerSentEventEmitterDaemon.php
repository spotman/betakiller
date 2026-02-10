<?php

declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Event\ServerSentEventInterface;
use BetaKiller\Event\SseGreetingEvent;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\MessageBus\EventBus;
use BetaKiller\MessageBus\OutboundEventMessageInterface;
use BetaKiller\MessageBus\OutboundEventTransportInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Service\AuthService;
use BetaKiller\Session\SessionStorageInterface;
use BetaKiller\Sse\ClientConnection;
use Fig\Http\Message\StatusCodeInterface;
use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Promise\PromiseInterface;
use React\Socket\SocketServer;
use React\Stream\ThroughStream;
use Throwable;

use function json_encode;
use function React\Promise\reject;
use function React\Promise\resolve;

/**
 * Class SseEsbBridgeDaemon
 *
 * @package BetaKiller\Daemon
 */
final class ServerSentEventEmitterDaemon extends AbstractDaemon
{
    public const CODENAME = 'ServerSentEventEmitter';

    /**
     * @var ClientConnection[]
     */
    private array $clients = [];

    /**
     * @var UserInterface[]
     */
    private array $users = [];

    public function __construct(
        private readonly SessionStorageInterface $requestSessionStorage,
        private readonly AuthService $auth,
        private readonly OutboundEventTransportInterface $transport,
        private readonly LoggerInterface $logger
    ) {
    }

    public function startDaemon(LoopInterface $loop): PromiseInterface
    {
        $this->configureSseServer($loop);
        $this->bindEsbHandler($loop);

        return resolve();
    }

    public function stopDaemon(LoopInterface $loop): PromiseInterface
    {
        $this->unbindEsbHandler($loop);
        $this->disconnectSseClients($loop);

        return resolve();
    }

    private function configureSseServer(LoopInterface $loop): void
    {
        $http = new HttpServer($loop, function (ServerRequestInterface $request) use ($loop) {
            if ($request->getUri()->getPath() === '/status') {
                return new Response(
                    StatusCodeInterface::STATUS_NOT_IMPLEMENTED,
                    ['Content-Type' => 'text/html'],
                    'Not Implemented'
                );
            }

            $this->logger->debug('Client connected');

            if (!$request->hasHeader('User-Agent')) {
                return new Response(StatusCodeInterface::STATUS_BAD_REQUEST);
            }

            $id = $request->getHeaderLine('Last-Event-ID');

            $reqSession = $this->getRequestSession($request);

            if (!SessionHelper::hasUserID($reqSession)) {
                return new Response(StatusCodeInterface::STATUS_UNAUTHORIZED);
            }

            $stream = new ThroughStream();
            $client = new ClientConnection($reqSession, $stream);

            $sessionId = $client->getId();

            // Check for duplicate sessions
            if ($this->hasClient($client)) {
                $this->logger->warning('Duplicate connection for Client Session ":id"', [
                    ':id' => $sessionId,
                ]);

                return new Response(
                    StatusCodeInterface::STATUS_BAD_REQUEST,
                    [],
                    'Bad Request'
                );
            }

            // Register new session (onConnect)
            $this->addClient($client);

            // Register session close handler (onDisconnect)
            $stream->on('close', function () use ($stream, $client) {
                $this->logger->debug('Client disconnected');

                $this->removeClient($client);
            });

            // Wait for response to be initialized (messages are not sent otherwise)
            $loop->futureTick(function () use ($client) {
                // Retry interval
                $this->sendRetry($client);

                // Initial greeting
                $this->forwardEventTo(new SseGreetingEvent(), $client);
            });

            return new Response(
                StatusCodeInterface::STATUS_OK,
                [
                    'Content-Type'      => 'text/event-stream',
                    'Cache-Control'     => 'no-cache',
                    'Connection'        => 'keep-alive',
                    'X-Accel-Buffering' => 'no',
                ],
                $stream
            );
        });

        $ip   = '0.0.0.0';
        $port = 8090;
        $uri  = $ip.':'.$port;

        $socket = new SocketServer($uri, [], $loop);

        $http->listen($socket);

        $this->logger->info('SSE Server is bound to :uri', [
            ':uri' => $uri,
        ]);
    }

    private function getRequestSession(ServerRequestInterface $request): SessionInterface
    {
        return $this->requestSessionStorage->initializeSessionFromRequest($request);
    }

    private function getClientUser(ClientConnection $client): UserInterface
    {
        // Cache User instances
        return $this->users[$client->getId()] ??= $this->auth->getSessionUser($client->session);
    }

    private function hasClient(ClientConnection $client): bool
    {
        return isset($this->clients[$client->getId()]);
    }

    private function addClient(ClientConnection $client): void
    {
        $this->clients[$client->getId()] = $client;
    }

    private function removeClient(ClientConnection $client): void
    {
        $id = $client->getId();

        unset($this->clients[$id], $this->users[$id]);
    }

    private function disconnectSseClients(LoopInterface $loop): void
    {
        foreach ($this->clients as $client) {
            $client->stream->close();
        }
    }

    private function bindEsbHandler(LoopInterface $loop): void
    {
        $this->transport->subscribeAnyOutbound(function (OutboundEventMessageInterface $event) {
            $this->markAsProcessing();

            $result = $this->forwardEvent($event);

            $this->markAsIdle();

            return $result;
        });

        $this->transport->startConsuming($loop);
    }

    private function unbindEsbHandler(LoopInterface $loop): void
    {
        $this->transport->stopConsuming($loop);
    }

    private function forwardEvent(OutboundEventMessageInterface $event): PromiseInterface
    {
        // Ignore non-SSE messages
        if (!$event instanceof ServerSentEventInterface) {
            return reject();
        }

        $user = null;

        try {
            $name = $event->getOutboundName();
            $data = $event->getOutboundData();

            $this->logger->debug('Received ":name" message from ESB with data :data', [
                ':name' => $name,
                ':data' => json_encode($data, JSON_THROW_ON_ERROR),
            ]);

            foreach ($this->clients as $client) {
                $user = $this->getClientUser($client);

                if (!EventBus::isMessageAllowedTo($event, $user)) {
                    continue;
                }

                $this->forwardEventTo($event, $client);
            }

            return resolve();
        } catch (Throwable $e) {
            $user
                ? LoggerHelper::logUserException($this->logger, $e, $user)
                : LoggerHelper::logRawException($this->logger, $e);

            return reject($e);
        }
    }

    private function forwardEventTo(OutboundEventMessageInterface $event, ClientConnection $client): void
    {
        $client->stream->write(sprintf('event: %s'.PHP_EOL, $event->getOutboundName()));
        $data = $event->getOutboundData();

        if ($data !== null) {
            $client->stream->write(sprintf('data: %s'.PHP_EOL.PHP_EOL, json_encode($data)));
        }
    }

    private function sendRetry(ClientConnection $client): void
    {
        $client->stream->write(sprintf('retry: %d'.PHP_EOL.PHP_EOL, 5));
    }
}
