<?php
namespace BetaKiller\Error;

use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Log\LazyLoadProxyHandler;
use BetaKiller\Log\LoggerInterface;
use BetaKiller\Log\ContextCleanupFormatter;
use BetaKiller\ModuleInitializerInterface;
use Monolog\Handler\PHPConsoleHandler;
use PhpConsole\Connector;
use PhpConsole\Storage\File;
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
        // Enable debugging via PhpConsole
        if ($this->appEnv->isDebugEnabled() && $this->isPhpConsoleActive()) {
            $this->initPhpConsole();
        } elseif ($this->appEnv->inProductionMode() || $this->appEnv->inStagingMode()) {
            $this->initPhpExceptionStorage();
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function isPhpConsoleActive(): bool
    {
        $storageFileName = $this->appEnv->getModeName().'.'.$this->appEnv->getRevisionKey().'.phpConsole.data';
        $storagePath     = $this->appEnv->getTempDirectory().DIRECTORY_SEPARATOR.$storageFileName;

        // Can be called only before PhpConsole\Connector::getInstance() and PhpConsole\Handler::getInstance()
        Connector::setPostponeStorage(new File($storagePath));

        return Connector::getInstance()->isActiveClient();
    }

    /**
     * @throws \Exception
     */
    private function initPhpConsole(): void
    {
        $phpConsoleHandler = new PHPConsoleHandler([
            'detectDumpTraceAndSource' => true,     // Autodetect and append trace data to debug
            'useOwnErrorsHandler'      => false,    // Enable errors handling
            'useOwnExceptionsHandler'  => false,    // Enable exceptions handling
        ]);

        $phpConsoleHandler->setFormatter(new ContextCleanupFormatter());

        $this->logger->pushHandler($phpConsoleHandler);
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
        $this->logger->pushHandler(new LazyLoadProxyHandler($factory, PhpExceptionStorageHandler::MIN_LEVEL));
    }
}
