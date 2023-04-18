<?php
namespace BetaKiller\Error;

use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Log\LoggerInterface;
use BetaKiller\Log\SkipExpectedExceptionsHandler;
use BetaKiller\ModuleInitializerInterface;

final class ErrorModuleInitializer implements ModuleInitializerInterface
{
    /**
     * @var \BetaKiller\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * @var \BetaKiller\Error\PhpExceptionStorageHandler
     */
    private PhpExceptionStorageHandler $handler;

    /**
     * ErrorModuleInitializer constructor.
     *
     * @param \BetaKiller\Env\AppEnvInterface              $appEnv
     * @param \BetaKiller\Error\PhpExceptionStorageHandler $handler
     * @param \BetaKiller\Log\LoggerInterface              $logger
     */
    public function __construct(
        AppEnvInterface            $appEnv,
        PhpExceptionStorageHandler $handler,
        LoggerInterface            $logger
    ) {
        $this->logger  = $logger;
        $this->appEnv  = $appEnv;
        $this->handler = $handler;
    }

    public function initModule(): void
    {
        if ($this->appEnv->inDevelopmentMode() && !$this->appEnv->isDebugEnabled()) {
            return;
        }

        // PhpExceptionStorage handler
        $this->logger->pushHandler(
            new SkipExpectedExceptionsHandler(
                $this->handler
            )
        );
    }
}
