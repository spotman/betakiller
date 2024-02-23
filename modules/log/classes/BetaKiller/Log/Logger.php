<?php
namespace BetaKiller\Log;

use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Helper\LoggerHelper;
use Monolog\ErrorHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TelegramBotHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerTrait;

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
        $errorHandler->registerErrorHandler([], $this->appEnv->isCli());
        $errorHandler->registerFatalHandler();

        // Do not register Monolog exception handler coz it calls exit()
        \set_exception_handler(function (\Throwable $e) {
            LoggerHelper::logRawException($this, $e);

            // Exit with error code in CLI mode
            if ($this->appEnv->isCli()) {
                exit(1);
            }
        });

        $isDebug = $this->appEnv->isDebugEnabled();
        $isHuman = $this->appEnv->isHuman();

//        if (!$isDebug) {
//            // GDPR processors first
//            $monolog->pushProcessor(new GdprProcessor());
//        }

        // Common processors next
        $monolog
            ->pushProcessor(new KohanaPlaceholderProcessor())
            ->pushProcessor(new MemoryPeakUsageProcessor())
            ->pushProcessor(new IntrospectionProcessor($monolog::WARNING, [], 3));

        // CLI mode logging
        if ($this->appEnv->isCli()) {
            $level = $isDebug ? \Monolog\Logger::DEBUG : \Monolog\Logger::NOTICE;

            $monolog->pushHandler(new StdOutHandler($level, $isHuman));

            if (DesktopNotificationHandler::isSupported()) {
                $monolog->pushHandler(new SkipExpectedExceptionsHandler(new DesktopNotificationHandler));
            }

            $monolog->pushProcessor(new CliProcessor);
        } else {
            $monolog->pushProcessor(new WebProcessor());
        }

        // File logging
        $logFilePath = $this->appEnv->getLogsPath(implode(DIRECTORY_SEPARATOR, [
            date('Y'),
            date('m'),
            date('d').'.log',
        ]));

        $logsLevel = $isDebug ? $monolog::DEBUG : $monolog::NOTICE;
//        $triggerLevel = $isDebug ? $monolog::DEBUG : $monolog::WARNING;

        $fileHandler = new StreamHandler($logFilePath, $logsLevel);
        $fileHandler->pushProcessor(new ContextCleanupProcessor);
        $fileHandler->pushProcessor(new ExceptionStacktraceProcessor);
        $monolog->pushHandler(new SkipExpectedExceptionsHandler($fileHandler));
//        $monolog->pushHandler(new SkipExpectedExceptionsHandler(new FingersCrossedHandler($fileHandler, $triggerLevel)));

        $slackWebHookUrl = $this->appEnv->getEnvVariable('SLACK_ERROR_WEBHOOK');
        $tgApiKey        = $this->appEnv->getEnvVariable('TELEGRAM_ERROR_API_KEY');
        $tgChannel       = $this->appEnv->getEnvVariable('TELEGRAM_ERROR_CHANNEL');

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
                    \Monolog\Logger::NOTICE
                );
                break;

            case $tgApiKey && $tgChannel:
                $errorHandler = new TelegramBotHandler(
                    $tgApiKey,
                    $tgChannel,
                    \Monolog\Logger::NOTICE,
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
                throw new \LogicException('Error notification handler must be specified');
        }

        $errorHandler->pushProcessor(new ContextCleanupProcessor);

        // Remove duplicate errors in debugging mode
        if ($isDebug) {
            $errorStorage = $this->appEnv->getTempPath('monolog-deduplication.storage');

            if (!\is_file($errorStorage)) {
                touch($errorStorage) && \chmod($errorStorage, 0660);
            }

            $errorHandler = new DeduplicationHandler(
                $errorHandler,
                $errorStorage,
                \Monolog\Logger::NOTICE,
                180 // Repeat notification in 3 minutes
            );
        }

        $monolog->pushHandler(new SkipExpectedExceptionsHandler($errorHandler));

        if ($this->appEnv->isDebugEnabled() && !$this->appEnv->inDevelopmentMode()) {
            $monolog->debug('Running :name env', [
                ':name' => $this->appEnv->getModeName(),
            ]);
        }

        return $monolog;
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
}
