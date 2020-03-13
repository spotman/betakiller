<?php
namespace BetaKiller\Error;

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
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * Initializer constructor.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param \BetaKiller\Log\LoggerInterface   $logger
     */
    public function __construct(
        ContainerInterface $container,
        LoggerInterface $logger
    ) {
        $this->logger    = $logger;
        $this->container = $container;
    }

    /**
     * @throws \BetaKiller\Exception
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function init(): void
    {
        $this->initPhpExceptionStorage();
    }

    /**
     * @throws \BetaKiller\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function initPhpExceptionStorage(): void
    {
        $factory = function () {
            return $this->container->get(PhpExceptionStorageHandler::class);
        };

        // PhpExceptionStorage handler
        $this->logger->pushHandler(
            new FilterExceptionsHandler(
                new LazyLoadProxyHandler($factory, PhpExceptionStorageHandler::MIN_LEVEL)
            )
        );
    }
}
