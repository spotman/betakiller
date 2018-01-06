<?php
namespace BetaKiller\Error;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Exception\ExceptionHandlerInterface;
use BetaKiller\Helper\AppEnv;
use BetaKiller\Log\LazyLoadProxyHandler;
use BetaKiller\Log\Logger;
use BetaKiller\Log\StripExceptionFromContextFormatter;
use BetaKiller\ModuleInitializerInterface;
use Monolog\Handler\PHPConsoleHandler;
use PhpConsole\Connector;
use PhpConsole\Storage\File;
use Psr\Container\ContainerInterface;

class Initializer implements ModuleInitializerInterface
{
    /**
     * @var \BetaKiller\Log\Logger
     */
    private $logger;

    /**
     * @var \BetaKiller\Helper\AppEnv
     */
    private $appEnv;

    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @var \BetaKiller\Exception\ExceptionHandlerInterface
     */
    private $handler;

    /**
     * Initializer constructor.
     *
     * @param \Psr\Container\ContainerInterface               $container
     * @param \BetaKiller\Log\Logger                          $logger
     * @param \BetaKiller\Helper\AppEnv                       $appEnv
     * @param \BetaKiller\Config\AppConfigInterface           $appConfig
     * @param \BetaKiller\Exception\ExceptionHandlerInterface $handler
     */
    public function __construct(
        ContainerInterface $container,
        Logger $logger,
        AppEnv $appEnv,
        AppConfigInterface $appConfig,
        ExceptionHandlerInterface $handler
    ) {
        $this->logger    = $logger;
        $this->container = $container;
        $this->appEnv    = $appEnv;
        $this->appConfig = $appConfig;
        $this->handler   = $handler;
    }

    /**
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function init(): void
    {
        $this->registerExceptionHandler();

        // Enable debugging via PhpConsole in browser mode
        if ($this->appEnv->isDebugEnabled() && !$this->appEnv->isCLI()) {
            $this->initPhpConsole();
        } elseif ($this->appEnv->inProduction(true)) {
            $this->initPhpExceptionStorage();
        }
    }

    private function registerExceptionHandler(): void
    {
        \Kohana_Exception::setHandler($this->handler);
    }

    /**
     * @throws \Exception
     */
    private function initPhpConsole(): void
    {
        $storageFileName = $this->appConfig->getNamespace().'.'.$this->appEnv->getModeName().'.phpConsole.data';
        $storagePath     = \sys_get_temp_dir().DIRECTORY_SEPARATOR.$storageFileName;

        // Can be called only before PhpConsole\Connector::getInstance() and PhpConsole\Handler::getInstance()
        Connector::setPostponeStorage(new File($storagePath));

        if (Connector::getInstance()->isActiveClient()) {
            $phpConsoleHandler = new PHPConsoleHandler([
                'detectDumpTraceAndSource' => true,     // Autodetect and append trace data to debug
                'useOwnErrorsHandler'      => false,    // Enable errors handling
                'useOwnExceptionsHandler'  => false,    // Enable exceptions handling
            ]);

            $phpConsoleHandler->setFormatter(new StripExceptionFromContextFormatter());

            $this->logger->pushHandler($phpConsoleHandler);
        }
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function initPhpExceptionStorage(): void
    {
        $logger = $this->logger;
        $container = $this->container;

        $factory = function () use ($logger, $container) {
            $handler = $container->get(PhpExceptionStorageHandler::class);

            return $handler->setLogger($logger);
        };

        // PhpExceptionStorage handler
        $this->logger->pushHandler(new LazyLoadProxyHandler($factory, PhpExceptionStorageHandler::MIN_LEVEL));
    }
}
