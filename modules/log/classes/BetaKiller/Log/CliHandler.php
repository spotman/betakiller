<?php
namespace BetaKiller\Log;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class CliHandler extends StreamHandler
{
    /**
     * CliHandler constructor.
     *
     * @param bool $isDebugAllowed
     * @param bool $ansi
     *
     * @throws \Exception
     */
    public function __construct(bool $isDebugAllowed, bool $ansi)
    {
        $level = $isDebugAllowed ? Logger::DEBUG : Logger::INFO;

        parent::__construct('php://stdout', $level);

        $formatter = $ansi
            ? new CliFormatter($isDebugAllowed)
            : new LineFormatter();

        $this->setFormatter($formatter);
    }
}
