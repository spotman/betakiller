<?php
namespace BetaKiller\Log;

use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\LoggerHelperTrait;
use Monolog\ErrorHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerTrait;

class Logger implements LoggerInterface
{
    use LoggerTrait;
    use LoggerHelperTrait;

    public const CONTEXT_KEY_EXCEPTION = 'exception';
    public const CONTEXT_KEY_REQUEST   = 'request';

    /**
     * @var \Monolog\Logger
     */
    private $monolog;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * Logger constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface $env
     *
     * @throws \Exception
     */
    public function __construct(AppEnvInterface $env)
    {
        $this->appEnv  = $env;
        $this->monolog = $this->makeMonologInstance();
    }

    /**
     * @return \Monolog\Logger
     * @throws \Exception
     */
    private function makeMonologInstance(): \Monolog\Logger
    {
        $monolog = new \Monolog\Logger('default');

        $errorHandler = new ErrorHandler($monolog);
        $errorHandler->registerErrorHandler();
        $errorHandler->registerFatalHandler();

        // Do not register Monolog exception handler coz it calls exit()
        \set_exception_handler([$this, 'exceptionHandler']);

        $isDebug = $this->appEnv->isDebugEnabled();

        // CLI mode logging
        if ($this->appEnv->isCli()) {
            $cliHandler = new CliHandler($isDebug);
            $monolog->pushHandler($cliHandler);
            $monolog->pushProcessor(new CliProcessor);

            if (DesktopNotificationHandler::isSupported()) {
                $monolog->pushHandler(new DesktopNotificationHandler);
            }
        } else {
            $monolog->pushProcessor(new WebProcessor());
        }

        // File logging
        $logFilePath = implode(DIRECTORY_SEPARATOR, [
            $this->appEnv->getAppRootPath(),
            'logs',
            date('Y'),
            date('m'),
            date('d').'.log',
        ]);

        $logsLevel = $isDebug ? $monolog::DEBUG : $monolog::NOTICE;

        $fileHandler = new StreamHandler($logFilePath, $monolog::DEBUG);
        $fileHandler->pushProcessor(new ContextCleanupProcessor);
        $fileHandler->pushProcessor(new ExceptionStacktraceProcessor);
        $monolog->pushHandler(new FingersCrossedHandler($fileHandler, $logsLevel));

        // Common processors
        $monolog
            ->pushProcessor(new KohanaPlaceholderProcessor())
            ->pushProcessor(new MemoryPeakUsageProcessor())
            ->pushProcessor(new IntrospectionProcessor($monolog::WARNING));

        return $monolog;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function log($level, $message, array $context = null): void
    {
        // Proxy to selected logger
        $this->monolog->log($level, $message, $context ?? []);
    }

    /**
     * @param \Monolog\Handler\HandlerInterface $handler
     */
    public function pushHandler(HandlerInterface $handler): void
    {
        $this->monolog->pushHandler($handler);
    }

    public function exceptionHandler($e): void
    {
        $this->logException($this, $e);

        // Exit with error code in CLI mode
        if ($this->appEnv->isCli()) {
            exit(1);
        }
    }
}
