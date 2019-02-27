<?php
namespace BetaKiller\Log;

use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Bramus\Monolog\Formatter\ColorSchemes\DefaultScheme;

class CliFormatter extends ColoredLineFormatter
{
    /**
     * @var bool
     */
    private $traceExceptions;

    public function __construct(bool $traceExceptions)
    {
        parent::__construct(new DefaultScheme(), "%message%\n");

        $this->traceExceptions = $traceExceptions;
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record): string
    {
        $output = parent::format($record);

        /** @var \Throwable|null $exception */
        $exception = $record['context']['exception'] ?? null;

        if ($this->traceExceptions && $exception) {

            while ($exception->getPrevious()) {
                $exception = $exception->getPrevious();
            }

            $output .= $exception->getTraceAsString();
        }

        return $output;
    }
}
