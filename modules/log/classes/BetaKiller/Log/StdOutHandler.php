<?php
namespace BetaKiller\Log;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

class StdOutHandler extends StreamHandler
{
    /**
     * StdOutHandler constructor.
     *
     * @param int  $level
     * @param bool $ansi
     *
     * @throws \Exception
     */
    public function __construct(int $level, bool $ansi)
    {
        parent::__construct('php://stdout', $level);

        $formatter = $ansi
            ? new CliFormatter(true)
            : new LineFormatter();

        $this->setFormatter($formatter);
    }
}
