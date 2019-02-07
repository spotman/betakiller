<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Config\WampConfig;
use BetaKiller\Wamp\WampInternalClient;
use BetaKiller\Wamp\WampUserDb;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use Thruway\Authentication\AuthenticationManager;
use Thruway\Authentication\WampCraAuthProvider;
use Thruway\Peer\Router;
use Thruway\Transport\RatchetTransportProvider;

class WampRouterDaemon implements DaemonInterface
{
    public const CODENAME = 'WampRouter';

    /**
     * @var \BetaKiller\Wamp\WampInternalClient
     */
    private $wampClient;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Config\WampConfig
     */
    private $wampConfig;

    /**
     * @var \BetaKiller\Wamp\WampUserDb
     */
    private $wampUserDb;

    /**
     * @var \Thruway\Peer\RouterInterface
     */
    private $router;

    /**
     * @param \BetaKiller\Config\WampConfig       $wampConfig
     * @param \BetaKiller\Wamp\WampInternalClient $wampClient
     * @param \BetaKiller\Wamp\WampUserDb         $wampUserDb
     * @param \Psr\Log\LoggerInterface            $logger
     */
    public function __construct(
        WampConfig $wampConfig,
        WampInternalClient $wampClient,
        WampUserDb $wampUserDb,
        LoggerInterface $logger
    ) {
        $this->wampConfig = $wampConfig;
        $this->wampClient = $wampClient;
        $this->wampUserDb = $wampUserDb;
        $this->logger     = $logger;
    }

    public function start(LoopInterface $loop): void
    {
        \Thruway\Logging\Logger::set($this->logger);

        $this->router = new Router($loop);

        // transport
        $this->router->addTransportProvider(new RatchetTransportProvider(
            $this->wampConfig->getConnectionHost(),
            $this->wampConfig->getConnectionPort()
        ));

        // auth manager
        $authMgr = new AuthenticationManager();
        $this->router->registerModule($authMgr);

        // user db
        $authProvClient = new WampCraAuthProvider([$this->wampConfig->getRealmName()]);
        $authProvClient->setUserDb($this->wampUserDb);
        $this->router->addInternalClient($authProvClient);

        // client
        $this->router->addInternalClient($this->wampClient);

        // Prepare to start (loop would be launched by the Run task)
        $this->router->start(false);
    }

    public function stop(): void
    {
        $this->router->stop(true);
    }
}
