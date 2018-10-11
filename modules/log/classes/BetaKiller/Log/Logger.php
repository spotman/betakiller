<?php
namespace BetaKiller\Log;

use BetaKiller\Helper\AppEnvInterface;
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

        $isDebug = $this->appEnv->isDebugEnabled();

        // CLI mode logging
        if ($this->appEnv->isCLI()) {
            $monolog->pushHandler(new CliHandler($isDebug));
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

        $monolog->pushHandler(new FingersCrossedHandler(
            new StreamHandler($logFilePath, $monolog::DEBUG),
            $logsLevel
        ));

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
        $this->monolog->log($level, $message, $context);
    }

    public function getMonolog(): \Monolog\Logger
    {
        return $this->monolog;
    }

    /**
     * @param \Monolog\Handler\HandlerInterface $handler
     */
    public function pushHandler(HandlerInterface $handler): void
    {
        $this->monolog->pushHandler($handler);
    }
}
