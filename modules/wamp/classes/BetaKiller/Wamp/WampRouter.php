<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

use Psr\Log\LoggerInterface;
use Thruway\Authentication\AuthenticationManager;
use Thruway\Authentication\WampCraAuthProvider;
use Thruway\Peer\Router;
use Thruway\Transport\RatchetTransportProvider;
use BetaKiller\Config\WampConfig;

/**
 * https://github.com/voryx/Thruway#php-client-example
 * https://github.com/voryx/Thruway/blob/master/Examples/InternalClient/RouterWtihInternalClient.php
 * https://github.com/voryx/Thruway/tree/master/Examples/Authentication/WampCra
 */
class WampRouter implements WampRouterInterface
{
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
     * @param \Psr\Log\LoggerInterface      $logger
     * @param \BetaKiller\Config\WampConfig $wampConfig
     * @param \BetaKiller\Wamp\WampClient   $wampClient
     * @param \BetaKiller\Wamp\WampUserDb   $wampUserDb
     */
    public function __construct(
        LoggerInterface $logger,
        WampConfig $wampConfig,
        WampClient $wampClient,
        WampUserDb $wampUserDb
    ) {
        $this->logger     = $logger;
        $this->wampConfig = $wampConfig;
        $this->wampClient = $wampClient;
        $this->wampUserDb = $wampUserDb;
    }

    public function run(): void
    {
        \Thruway\Logging\Logger::set($this->logger);

        $router = new Router();

        // transport
        $transportProvider = new RatchetTransportProvider(
            $this->wampConfig->getConnectionHost(),
            $this->wampConfig->getConnectionPort()
        );
        $router->registerModule($transportProvider);

        // client
        $router->addInternalClient($this->wampClient);

        // auth manager
        $authMgr = new AuthenticationManager();
        $router->registerModule($authMgr);

        // user db
        $authProvClient = new WampCraAuthProvider([$this->wampConfig->getRealmName()]);
        $authProvClient->setUserDb($this->wampUserDb);
        $router->addInternalClient($authProvClient);

        // start
        $router->start();
    }
}
