<?php


namespace BetaKiller\Log;


use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Bramus\Monolog\Formatter\ColorSchemes\DefaultScheme;

class CliFormatter extends ColoredLineFormatter
{
    /**
     * @var bool
     */
    private $isDebugEnabled;

    public function __construct(bool $isDebugEnabled) {
        parent::__construct(new DefaultScheme(), "%message%\n");

        $this->isDebugEnabled = $isDebugEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $output = parent::format($record);

        /** @var \Throwable|null $exception */
        $exception = $record['context']['exception'] ?? null;

        if ($this->isDebugEnabled && $exception) {
            $output .= $exception->getTraceAsString();

            $previous = $exception->getPrevious();

            if ($previous) {
                $output .= PHP_EOL.PHP_EOL.$previous->getTraceAsString();
            }
        }

        return $output;
    }
}
