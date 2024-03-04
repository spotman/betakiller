<?php
namespace BetaKiller\Log;

use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Helper\LoggerHelper;
use Monolog\ErrorHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TelegramBotHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerTrait;
use Throwable;
use function chmod;
use function is_file;
use function set_exception_handler;

class Logger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var \Monolog\Logger
     */
    private \Monolog\Logger $monolog;

    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * Logger constructor.
     *
     * @param \BetaKiller\Env\AppEnvInterface $env
     *
     * @throws \Exception
     */
    public function __construct(AppEnvInterface $env)
    {
        $this->appEnv  = $env;
        $this->monolog = new \Monolog\Logger('default');

        $this->init();
    }

    /**
     * @throws \Exception
     */
    private function init(): void
    {
        $this->registerErrorHandlers();

        $this->addProcessors();

        $this->addStdOutHandler();
        $this->addDesktopHandler();
        $this->addLogsHandler();
        $this->addRealtimeHandler();

        if ($this->appEnv->isDebugEnabled() && !$this->appEnv->inDevelopmentMode()) {
            $this->monolog->debug('Running :name env', [
                ':name' => $this->appEnv->getModeName(),
            ]);
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed      $level
     * @param string     $message
     * @param array|null $context
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

    public function flushBuffers(): void
    {
        $this->monolog->close();
    }

    public function getMonologInstance(): \Monolog\Logger
    {
        return $this->monolog;
    }

    private function registerErrorHandlers(): void
    {
        $errorHandler = new ErrorHandler($this->monolog);
        $errorHandler->registerErrorHandler([], $this->appEnv->isCli());
        $errorHandler->registerFatalHandler();

        // Do not register Monolog exception handler coz it calls exit()
        set_exception_handler(function (Throwable $e) {
            LoggerHelper::logRawException($this, $e);

            // Exit with error code in CLI mode
            if ($this->appEnv->isCli()) {
                exit(1);
            }
        });
    }

    private function addProcessors(): void
    {
        $ignoreClasses = [
            '\\Logger',
            '\\LoggerHelper',
        ];

        // Common processors next
        $this->monolog
            ->pushProcessor(new KohanaPlaceholderProcessor())
            ->pushProcessor(new MemoryPeakUsageProcessor())
            ->pushProcessor(new IntrospectionProcessor($this->monolog::WARNING, $ignoreClasses));

        // CLI mode logging
        if ($this->appEnv->isCli()) {
            $this->monolog->pushProcessor(new CliProcessor);
        } else {
            $this->monolog->pushProcessor(new WebProcessor());
        }
    }

    private function getLogLevel(): int
    {
        $isDebug = $this->appEnv->isDebugEnabled();

        $level = $isDebug ? \Monolog\Logger::DEBUG : \Monolog\Logger::NOTICE;

        // CLI override
        if ($this->appEnv->isCli()) {
            $cliName = $this->appEnv->getCliOption(AppEnvInterface::CLI_OPTION_LOG_LEVEL);

            if ($cliName) {
                $level = $this->monolog::toMonologLevel($cliName);
            }
        }

        return $level;
    }

    private function addStdOutHandler(): void
    {
        if (!$this->appEnv->isCli()) {
            return;
        }

        $logLevel = $this->getLogLevel();
        $isHuman  = $this->appEnv->isHuman();

        $this->monolog->pushHandler(new StdOutHandler($logLevel, $isHuman));
    }

    private function addDesktopHandler(): void
    {
        if ($this->appEnv->isCli() && DesktopNotificationHandler::isSupported()) {
            $this->monolog->pushHandler(new SkipExpectedExceptionsHandler(new DesktopNotificationHandler));
        }
    }

    private function addLogsHandler(): void
    {
        // File logging
        $logFilePath = $this->appEnv->getLogsPath(implode(DIRECTORY_SEPARATOR, [
            date('Y'),
            date('m'),
            date('d').'.log',
        ]));

        $logLevel = $this->getLogLevel();

        $fileHandler = new StreamHandler($logFilePath, $logLevel);
        $fileHandler->pushProcessor(new ContextCleanupProcessor);
        $fileHandler->pushProcessor(new ExceptionStacktraceProcessor);

        $this->monolog->pushHandler(new SkipExpectedExceptionsHandler($fileHandler));
    }

    private function addRealtimeHandler(): void
    {
        $slackWebHookUrl = $this->appEnv->getEnvVariable('SLACK_ERROR_WEBHOOK');
        $tgApiKey        = $this->appEnv->getEnvVariable('TELEGRAM_ERROR_API_KEY');
        $tgChannel       = $this->appEnv->getEnvVariable('TELEGRAM_ERROR_CHANNEL');

        $logLevel = \Monolog\Logger::NOTICE;

        switch (true) {
            case $slackWebHookUrl:
                $errorHandler = new SlackWebhookHandler(
                    $slackWebHookUrl,
                    null,
                    'Errors Bot',
                    true,
                    ':interrobang:',
                    true,
                    true,
                    $logLevel
                );
                break;

            case $tgApiKey && $tgChannel:
                $errorHandler = new TelegramBotHandler(
                    $tgApiKey,
                    $tgChannel,
                    $logLevel,
                    true,
                    'HTML',
                    true,
                    false,
                    false,
                    true
                );

                $tgFormatter = new LineFormatter(
                    "%channel%.%level_name%:\n%message%\n%context%\n%extra%\n\n",
                    null,
                    true,
                    true,
                    true
                );
                $errorHandler->setFormatter($tgFormatter);
                break;

            default:
                return;
        }

        $errorHandler->pushProcessor(new ContextCleanupProcessor);

        // Remove duplicate errors
        $errorStorage = $this->appEnv->getTempPath('monolog-deduplication.storage');

        if (!is_file($errorStorage)) {
            touch($errorStorage) && chmod($errorStorage, 0660);
        }

        $this->monolog->pushHandler(new SkipExpectedExceptionsHandler(new DeduplicationHandler(
            $errorHandler,
            $errorStorage,
            $logLevel,
            180 // Repeat notification in 3 minutes
        )));
    }
}
