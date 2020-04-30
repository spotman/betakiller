<?php
namespace BetaKiller\Error;

use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Log\FilterExceptionsHandler;
use BetaKiller\Log\LoggerInterface;
use BetaKiller\ModuleInitializerInterface;

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
     * @var \BetaKiller\Error\PhpExceptionStorageHandler
     */
    private PhpExceptionStorageHandler $handler;

    /**
     * Initializer constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface           $appEnv
     * @param \BetaKiller\Error\PhpExceptionStorageHandler $handler
     * @param \BetaKiller\Log\LoggerInterface              $logger
     */
    public function __construct(
        AppEnvInterface $appEnv,
        PhpExceptionStorageHandler $handler,
        LoggerInterface $logger
    ) {
        $this->logger  = $logger;
        $this->appEnv  = $appEnv;
        $this->handler = $handler;
    }

    /**
     * @throws \BetaKiller\Exception
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function init(): void
    {
        if ($this->appEnv->inDevelopmentMode() && !$this->appEnv->isDebugEnabled()) {
            return;
        }

        $this->initPhpExceptionStorage();
    }

    /**
     * @throws \BetaKiller\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function initPhpExceptionStorage(): void
    {
//        $factory = function () {
//            return $this->container->get(PhpExceptionStorageHandler::class);
//        };

        // PhpExceptionStorage handler
        $this->logger->pushHandler(
            new FilterExceptionsHandler(
                $this->handler
//                new LazyLoadProxyHandler($factory, PhpExceptionStorageHandler::MIN_LEVEL)
            )
        );
    }
}
