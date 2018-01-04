<?php
namespace BetaKiller\Log;

use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Bramus\Monolog\Formatter\ColorSchemes\DefaultScheme;
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

        // Color scheme and formatter
        $cliFormatter = new ColoredLineFormatter(new DefaultScheme(), "%message%\n");
        $this->setFormatter($cliFormatter);
    }
}
