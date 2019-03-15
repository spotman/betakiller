<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Config\WampConfigInterface;
use BetaKiller\Wamp\InternalAuthProviderClient;
use BetaKiller\Wamp\WampRouter;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use Thruway\Authentication\AuthenticationManager;
use Thruway\Authentication\WampCraAuthProvider;
use Thruway\Authentication\WampCraUserDbInterface;
use Thruway\Transport\RatchetTransportProvider;

class WampRouterDaemon implements DaemonInterface
{
    public const CODENAME = 'WampRouter';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Config\WampConfigInterface
     */
    private $wampConfig;

    /**
     * @var \Thruway\Authentication\WampCraUserDbInterface
     */
    private $wampUserDb;

    /**
     * @var \Thruway\Peer\RouterInterface
     */
    private $router;

    /**
     * @param \BetaKiller\Config\WampConfigInterface         $wampConfig
     * @param \Thruway\Authentication\WampCraUserDbInterface $wampUserDb
     * @param \Psr\Log\LoggerInterface                       $logger
     */
    public function __construct(
        WampConfigInterface $wampConfig,
        WampCraUserDbInterface $wampUserDb,
        LoggerInterface $logger
    ) {
        $this->wampConfig = $wampConfig;
        $this->wampUserDb = $wampUserDb;
        $this->logger     = $logger;
    }

    public function start(LoopInterface $loop): void
    {
        \Thruway\Logging\Logger::set($this->logger);

        $this->router = new WampRouter($loop);

        // Transport
        $transport = new RatchetTransportProvider(
            $this->wampConfig->getConnectionHost(),
            $this->wampConfig->getConnectionPort()
        );
        $transport->enableKeepAlive($loop);
        $this->router->addTransportProvider($transport);

        // Auth manager
        $authMgr = new AuthenticationManager();
        $this->router->registerModule($authMgr);

        // External auth
        $extAuth = new WampCraAuthProvider(['*']);
        $extAuth->setUserDb($this->wampUserDb);
        $this->router->addInternalClient($extAuth);

        // Internal auth
        $intAuth = new InternalAuthProviderClient(['*']);
        $this->router->addInternalClient($intAuth);

        // Restart every 24h coz of annoying memory leak
        $loop->addTimer(60 * 1440, function () use ($loop) {
            $this->logger->info('Stopping router to prevent memory leaks');
            $this->stop();
            $loop->stop();
        });

        // Prepare to start (loop would be launched by the Run task)
        $this->router->start(false);
    }

    public function stop(): void
    {
        $this->router->stop(true);
    }
}
