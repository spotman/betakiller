<?php

namespace BetaKiller\Log;

use BetaKiller\Helper\LoggerHelper;
use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Bramus\Monolog\Formatter\ColorSchemes\DefaultScheme;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\LogRecord;

class StdOutHandler extends StreamHandler
{
    /**
     * StdOutHandler constructor.
     *
     * @param \Monolog\Level $level
     * @param bool           $isHuman
     */
    public function __construct(Level $level, bool $isHuman)
    {
        parent::__construct('php://stdout', $level);

        $formatter = $isHuman
            ? new ColoredLineFormatter(new DefaultScheme(), "%message%\n")
            : new LineFormatter();

        $this->setFormatter($formatter);
    }

    public function isHandling(LogRecord $record): bool
    {
        // Do not show exceptions, they will be processed in exception handler
        if (isset($record['context'][LoggerHelper::CONTEXT_KEY_EXCEPTION])) {
            return false;
        }

        return parent::isHandling($record);
    }
}
