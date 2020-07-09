<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Exception;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\MessageBus\OutboundEventMessageInterface;
use BetaKiller\MessageBus\OutboundEventTransportInterface;
use BetaKiller\Wamp\WampClient;
use BetaKiller\Wamp\WampClientBuilder;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use React\Promise\RejectedPromise;
use Throwable;
use Thruway\ClientSession;
use Thruway\Logging\Logger;

/**
 * Class WampEsbBridgeDaemon
 *
 * @package BetaKiller\Daemon
 */
class WampEsbBridgeDaemon implements DaemonInterface
{
    public const CODENAME = 'WampEsbBridge';

    /**
     * @var \BetaKiller\Wamp\WampClientBuilder
     */
    private WampClientBuilder $clientBuilder;

    /**
     * @var \BetaKiller\Wamp\WampClient
     */
    private WampClient $wampClient;

    /**
     * @var ClientSession|null
     */
    private ?ClientSession $clientSession = null;

    /**
     * @var \BetaKiller\MessageBus\OutboundEventTransportInterface
     */
    private OutboundEventTransportInterface $transport;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param \BetaKiller\Wamp\WampClientBuilder                     $clientFactory
     * @param \BetaKiller\MessageBus\OutboundEventTransportInterface $transport
     * @param \Psr\Log\LoggerInterface                               $logger
     */
    public function __construct(
        WampClientBuilder $clientFactory,
        OutboundEventTransportInterface $transport,
        LoggerInterface $logger
    ) {
        $this->clientBuilder = $clientFactory;
        $this->transport     = $transport;
        $this->logger        = $logger;
    }

    public function startDaemon(LoopInterface $loop): void
    {
        Logger::set($this->logger);

        // Restart every 24h coz of annoying memory leak
        $loop->addTimer(60 * 1440, function () use ($loop) {
            $this->logger->info('Stopping Wamp-to-ESB bridge worker to prevent memory leaks');
            $this->stopDaemon($loop);
            $loop->stop();
        });

        // Bind ESB event listener
        $this->transport->subscribeAnyOutbound(function (OutboundEventMessageInterface $event) {
            return $this->forwardEvent($event);
        });

        // Use internal auth and connection coz it is an internal worker
        $this->clientBuilder->internalConnection()->internalAuth();

        $this->wampClient = $this->clientBuilder->publicRealm()->create($loop);

//        $this->clientHelper->bindSessionHandlers($loop);

        // Register new session
        $this->wampClient->onSessionOpen(function (ClientSession $session) use ($loop) {
            // Close previous session (stale)
            if ($this->clientSession && !$this->clientSession->isGoodbyeSent()) {
                $this->clientSession->close();
            }

            // Store session for future use
            $this->clientSession = $session;

            $this->logger->debug('Opened WAMP session :id in ":realm" realm', [
                ':id'    => $session->getSessionId(),
                ':realm' => $session->getRealm(),
            ]);

            $this->transport->startConsuming($loop);
        });

        // Remove stale session
        $this->wampClient->onSessionClose(function () use ($loop) {
            if ($this->clientSession) {
                $this->transport->stopConsuming($loop);

                $this->clientSession = null;
            }
        });

        // Keep alive
        $this->wampClient->bindPingHandlers();

        $this->wampClient->start(false);

        $loop->run();
    }

    public function stopDaemon(LoopInterface $loop): void
    {
        $this->transport->stopConsuming($loop);

        // Stop client and disconnect
        $this->wampClient->onClose('Stopped');
    }

    private function forwardEvent(OutboundEventMessageInterface $event): PromiseInterface
    {
//        $user = null;

        try {
            $name = $event->getOutboundName();
            $data = $event->getOutboundData();

            $this->logger->debug('Received ":name" message from ESB with data :data', [
                ':name' => $name,
                ':data' => \json_encode($data, JSON_THROW_ON_ERROR),
            ]);

//            $wampSession = $this->clientHelper->getProcedureSession(func_get_args());
//            $user        = $this->clientHelper->getSessionUser($wampSession);

            if (!$this->clientSession) {
                throw new Exception('WAMP session is missing, can not forward ESB event');
            }

            if ($this->clientSession->getState() !== $this->clientSession::STATE_UP) {
                throw new Exception('WAMP session ":id" is not active, can not forward ESB event', [
                    ':id' => $this->clientSession->getSessionId(),
                ]);
            }

            $promise = $this->clientSession->publish($name, null, $data, [
                // TODO limit target users
                'exclude_me'  => true,
                'acknowledge' => true,
            ]);

            $promise->then(function () use ($name) {
                $this->logger->debug('Message ack for ":name" in ":realm" realm', [
                    ':name'  => $name,
                    ':realm' => $this->clientSession->getRealm(),
                ]);
            });

            $promise->otherwise(function () use ($name) {
                $this->logger->warning('Message ":name" send failed for ":target" at ":realm" realm', [
                    ':name'   => $name,
                    ':target' => $this->clientSession->getSessionId(),
                    ':realm'  => $this->clientSession->getRealm(),
                ]);
            });

            return $promise;
        } catch (Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);

            return new RejectedPromise;
        }
    }
}
