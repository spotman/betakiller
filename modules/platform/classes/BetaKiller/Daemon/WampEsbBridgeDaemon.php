<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Event\EsbExternalEventTransport;
use BetaKiller\Exception;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Wamp\WampClient;
use BetaKiller\Wamp\WampClientBuilder;
use BetaKiller\Wamp\WampClientHelper;
use Interop\Queue\Consumer;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use Throwable;
use Thruway\ClientSession;

class WampEsbBridgeDaemon implements DaemonInterface
{
    public const CODENAME = 'WampEsbBridge';

    /**
     * @var \BetaKiller\Wamp\WampClientBuilder
     */
    private $clientBuilder;

    /**
     * @var WampClient[]
     */
    private $wampClients = [];

    /**
     * @var ClientSession[]
     */
    private $clientSessions = [];

    /**
     * @var \BetaKiller\Wamp\WampClientHelper
     */
    private $clientHelper;

    /**
     * @var \Enqueue\Redis\RedisConsumer
     */
    private $consumer;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Interop\Queue\Context
     */
    private $context;

    /**
     * @param \BetaKiller\Wamp\WampClientBuilder $clientFactory
     * @param \BetaKiller\Wamp\WampClientHelper  $clientHelper
     * @param \Interop\Queue\Context             $context
     * @param \Psr\Log\LoggerInterface           $logger
     */
    public function __construct(
        WampClientBuilder $clientFactory,
        WampClientHelper $clientHelper,
        Context $context,
        LoggerInterface $logger
    ) {
        $this->clientBuilder = $clientFactory;
        $this->clientHelper  = $clientHelper;
        $this->context       = $context;
        $this->logger        = $logger;
    }

    public function start(LoopInterface $loop): void
    {
        \Thruway\Logging\Logger::set($this->logger);

        // Restart every 24h coz of annoying memory leak
        $loop->addTimer(60 * 1440, function () use ($loop) {
            $this->logger->info('Stopping Wamp-to-ESB bridge worker to prevent memory leaks');
            $this->stop();
            $loop->stop();
        });

        // Use internal auth and connection coz it is an internal worker
        $this->clientBuilder->internalConnection()->internalAuth();

        $this->wampClients = [
            // For processing internal clients in 'internal' realm (workers, checkers, etc)
            $this->clientBuilder->internalRealm()->create($loop),

            // For processing external clients in 'external' realm (public clients)
            $this->clientBuilder->publicRealm()->create($loop),
        ];

//        $this->clientHelper->bindSessionHandlers($loop);

        // Bind events and start every client
        foreach ($this->wampClients as $wampClient) {
            // Register new sessions
            $wampClient->on('open', function (ClientSession $session) {
                // Store session for future use
                $id                        = $session->getSessionId();
                $this->clientSessions[$id] = $session;

                $this->logger->debug('Opened WAMP session :id in ":realm" realm', [
                    ':id'    => $id,
                    ':realm' => $session->getRealm(),
                ]);
            });

            // Remove unused sessions
            $wampClient->onSessionClose(function (ClientSession $session) {
                $id = $session->getSessionId();

                if (isset($this->clientSessions[$id])) {
                    unset($this->clientSessions[$id]);
                }
            });

            $wampClient->start(false);
        }

        $this->listenForEsbEvents($loop);
    }

    public function stop(): void
    {
        // Close ESB connection first
        $this->context->close();

        // Stop clients and disconnect
        foreach ($this->wampClients as $wampClient) {
            $wampClient->onClose('Stopped');
        }
    }

    private function listenForEsbEvents(LoopInterface $loop): void
    {
        $topic = $this->context->createTopic(EsbExternalEventTransport::OUTBOUND_TOPIC_NAME);

        $consumer = $this->context->createConsumer($topic);

        $loop->addPeriodicTimer(0.1, function () use ($consumer) {
            // Check message
            $message = $consumer->receiveNoWait();

            if ($message) {
                // Ack first coz WAMP processing may take long time
//                $consumer->acknowledge($message);

                // process a message
                $this->forwardEvent($message, $consumer);
            }
        });
    }

    private function forwardEvent(Message $message, Consumer $consumer): bool
    {
//        $user = null;

        try {
            $name = $message->getProperty(EsbExternalEventTransport::PROPERTY_OUTBOUND_NAME);
            $data = (array)json_decode($message->getBody(), true);

            $this->logger->debug('Received ":name" message from ESB with data :data', [
                ':name' => $name,
                ':data' => $message->getBody(),
            ]);

//            $wampSession = $this->clientHelper->getProcedureSession(func_get_args());
//            $user        = $this->clientHelper->getSessionUser($wampSession);


            foreach ($this->clientSessions as $session) {
                if ($session->getState() !== $session::STATE_UP) {
                    throw new Exception('WAMP session ":id" is not active, can not send event', [
                        ':id' => $session->getSessionId(),
                    ]);
                }

                $promise = $session->publish($name, null, $data, [
                    // TODO limit target users
                    'exclude_me'  => true,
                    'acknowledge' => true,
                ]);

                $promise->then(function () use ($name, $message, $session, $consumer) {
                    $consumer->acknowledge($message);

                    $this->logger->debug('Message ack for ":name" in ":realm" realm', [
                        ':name'  => $name,
                        ':realm' => $session->getRealm(),
                    ]);
                });

                $promise->otherwise(function () use ($name, $consumer, $message, $session) {
                    $consumer->reject($message);

                    $this->logger->warning('Message ":name" send failed for ":target" at ":realm" realm', [
                        ':name'   => $name,
                        ':target' => $session->getSessionId(),
                        ':realm'  => $session->getRealm(),
                    ]);
                });
            }
        } catch (Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);

            return false;
        }

        return true;
    }
}
