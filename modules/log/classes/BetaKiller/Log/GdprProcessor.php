<?php
declare(strict_types=1);

namespace BetaKiller\Log;

use Egeniq\Monolog\Gdpr\Processor\RedactEmailProcessor;
use Egeniq\Monolog\Gdpr\Processor\RedactIpProcessor;

class GdprProcessor
{
    /**
     * @var callable[]
     */
    private $processors;

    /**
     * GdprProcessor constructor.
     */
    public function __construct()
    {
        $this->processors = [
            new RedactEmailProcessor(),
            new RedactIpProcessor(),
        ];
    }

    /**
     * The __invoke method is called when a script tries to call an object as a function.
     *
     * @param array $record
     *
     * @return mixed
     * @link https://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.invoke
     */
    public function __invoke(array $record)
    {
        // Preserve exception and request as is
        $e = $record['context'][Logger::CONTEXT_KEY_EXCEPTION] ?? null;
        $r = $record['context'][Logger::CONTEXT_KEY_REQUEST] ?? null;

        foreach ($this->processors as $proc) {
            $record = $proc($record);
        }

        // Restore exception and request
        if ($e) {
            $record['context'][Logger::CONTEXT_KEY_EXCEPTION] = $e;
        }

        if ($r) {
            $record['context'][Logger::CONTEXT_KEY_REQUEST] = $r;
        }

        return $record;
    }
}
