<?php
namespace BetaKiller\Log;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class CliHandler extends StreamHandler
{
    /**
     * CliHandler constructor.
     *
     * @param bool $isDebugAllowed
     *
     * @throws \Exception
     */
    public function __construct(bool $isDebugAllowed)
    {
        $level = $isDebugAllowed ? Logger::DEBUG : Logger::INFO;

        parent::__construct('php://stdout', $level);

        $this->setFormatter(new CliFormatter($isDebugAllowed));
    }
}
