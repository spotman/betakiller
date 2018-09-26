<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

use Psr\Log\LoggerInterface;
use Thruway\Peer\Router;
use Thruway\Transport\RatchetTransportProvider;
use BetaKiller\Config\WampConfig;

/**
 * https://github.com/voryx/Thruway#php-client-example
 * https://github.com/voryx/Thruway/blob/master/Examples/InternalClient/RouterWtihInternalClient.php
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
     * @param \Psr\Log\LoggerInterface      $logger
     * @param \BetaKiller\Config\WampConfig $wampConfig
     * @param \BetaKiller\Wamp\WampClient   $wampClient
     */
    public function __construct(LoggerInterface $logger, WampConfig $wampConfig, WampClient $wampClient)
    {
        $this->logger     = $logger;
        $this->wampConfig = $wampConfig;
        $this->wampClient = $wampClient;
    }

    public function run(): void
    {
        \Thruway\Logging\Logger::set($this->logger);

        $router            = new Router();
        $transportProvider = new RatchetTransportProvider(
            $this->wampConfig->getConnectionHost(),
            $this->wampConfig->getConnectionPort()
        );
        $router->registerModule($transportProvider);
        $router->addInternalClient($this->wampClient);
        $router->start();
    }
}
