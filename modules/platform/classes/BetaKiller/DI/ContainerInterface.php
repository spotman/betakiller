<?php
namespace BetaKiller\DI;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Log\LoggerInterface;
use DI\FactoryInterface;
use Invoker\InvokerInterface;

interface ContainerInterface extends \Psr\Container\ContainerInterface, FactoryInterface, InvokerInterface
{
    public function init(
        ConfigProviderInterface $configProvider,
        AppEnvInterface $appEnv,
        LoggerInterface $logger
    ): void;
}
