<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Exception;
use BetaKiller\Wamp\WampClient;
use BetaKiller\Wamp\WampClientBuilder;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use Thruway\ClientSession;
use Thruway\Logging\Logger;
use function React\Promise\resolve;

final class WampPingDaemon extends AbstractDaemon
{
    public const CODENAME = 'WampPing';

    private WampClient $wampClient;

    /**
     * @var \BetaKiller\Wamp\WampClientBuilder
     */
    private WampClientBuilder $clientBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * WampPingDaemon constructor.
     *
     * @param \BetaKiller\Wamp\WampClientBuilder $clientBuilder
     * @param \Psr\Log\LoggerInterface           $logger
     */
    public function __construct(
        WampClientBuilder $clientBuilder,
        LoggerInterface $logger
    ) {
        $this->clientBuilder = $clientBuilder;
        $this->logger        = $logger;
    }

    /**
     * @inheritDoc
     */
    public function startDaemon(LoopInterface $loop): PromiseInterface
    {
        Logger::set($this->logger);

        // Use internal auth and connection coz it is an internal worker
        $this->wampClient = $this->clientBuilder
            ->internalConnection()
            ->internalAuth()
            ->publicRealm()
            ->create($loop);

        $this->wampClient->onSessionOpen(static function (ClientSession $session) {
            $session->register(WampClient::RPC_PING, static function () {
                return [true];
            })->otherwise(static function () {
                throw new Exception('WAMP ping handler is not installed');
            });
        });

//        $this->wampClient->onSessionClose(static function () {
//            $session->unregister(WampClient::RPC_PING);
//        });

        $this->wampClient->start(false);

        return resolve();
    }

    /**
     * @inheritDoc
     */
    public function stopDaemon(LoopInterface $loop): PromiseInterface
    {
        // Stop client and disconnect
        $this->wampClient->setAttemptRetry(false);
        $this->wampClient->onClose('Stopped');

        return resolve();
    }
}
