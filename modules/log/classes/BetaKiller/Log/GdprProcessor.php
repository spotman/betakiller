<?php
declare(strict_types=1);

namespace BetaKiller\Log;

use BetaKiller\Helper\LoggerHelper;

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
            // TODO json serialization brakes monolog logic, we need to find another way to exclude sensitive data
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
        $e = $record['context'][LoggerHelper::CONTEXT_KEY_EXCEPTION] ?? null;
        $r = $record['context'][LoggerHelper::CONTEXT_KEY_REQUEST] ?? null;
        $u = $record['context'][LoggerHelper::CONTEXT_KEY_USER] ?? null;

        foreach ($this->processors as $proc) {
            $record = $proc($record);
        }

        // Restore exception, request and user
        if ($e) {
            $record['context'][LoggerHelper::CONTEXT_KEY_EXCEPTION] = $e;
        }

        if ($r) {
            $record['context'][LoggerHelper::CONTEXT_KEY_REQUEST] = $r;
        }

        if ($u) {
            $record['context'][LoggerHelper::CONTEXT_KEY_USER] = $u;
        }

        return $record;
    }
}
