<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Config\WampConfig;
use BetaKiller\Wamp\WampClient;
use BetaKiller\Wamp\WampUserDb;
use Psr\Log\LoggerInterface;
use Thruway\Authentication\AuthenticationManager;
use Thruway\Authentication\WampCraAuthProvider;
use Thruway\Peer\Router;
use Thruway\Transport\RatchetTransportProvider;

class WampRouterDaemon implements DaemonInterface
{
    public const CODENAME = 'WampRouter';

    /**
     * @var \BetaKiller\Wamp\WampClient
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
     * @param \BetaKiller\Config\WampConfig $wampConfig
     * @param \BetaKiller\Wamp\WampClient   $wampClient
     * @param \BetaKiller\Wamp\WampUserDb   $wampUserDb
     * @param \Psr\Log\LoggerInterface      $logger
     */
    public function __construct(
        WampConfig $wampConfig,
        WampClient $wampClient,
        WampUserDb $wampUserDb,
        LoggerInterface $logger
    ) {
        $this->wampConfig = $wampConfig;
        $this->wampClient = $wampClient;
        $this->wampUserDb = $wampUserDb;
        $this->logger     = $logger;
    }

    public function start(): void
    {
        \Thruway\Logging\Logger::set($this->logger);

        $this->router = new Router();

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

        // start
        $this->router->start();
    }

    public function stop(): void
    {
        $this->router->stop(true);
    }
}
