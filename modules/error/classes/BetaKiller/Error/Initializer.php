<?php
namespace BetaKiller\Error;

use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Log\FilterExceptionsHandler;
use BetaKiller\Log\LazyLoadProxyHandler;
use BetaKiller\Log\LoggerInterface;
use BetaKiller\ModuleInitializerInterface;
use Psr\Container\ContainerInterface;

class Initializer implements ModuleInitializerInterface
{
    /**
     * @var \BetaKiller\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * Initializer constructor.
     *
     * @param \Psr\Container\ContainerInterface  $container
     * @param \BetaKiller\Log\LoggerInterface    $logger
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     */
    public function __construct(
        ContainerInterface $container,
        LoggerInterface $logger,
        AppEnvInterface $appEnv
    ) {
        $this->logger    = $logger;
        $this->container = $container;
        $this->appEnv    = $appEnv;
    }

    /**
     * @throws \BetaKiller\Exception
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function init(): void
    {
        if ($this->appEnv->inProductionMode() || $this->appEnv->inStagingMode()) {
            $this->initPhpExceptionStorage();
        }
    }

    /**
     * @throws \BetaKiller\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function initPhpExceptionStorage(): void
    {
        $logger    = $this->logger;
        $container = $this->container;

        $factory = function () use ($logger, $container) {
            $handler = $container->get(PhpExceptionStorageHandler::class);

            $handler->setLogger($logger);

            return $handler;
        };

        // PhpExceptionStorage handler
        $this->logger->pushHandler(
            new FilterExceptionsHandler(
                new LazyLoadProxyHandler($factory, PhpExceptionStorageHandler::MIN_LEVEL)
            )
        );
    }
}
