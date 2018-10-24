<?php
namespace BetaKiller\Log;

class CliProcessor
{
    /**
     * @param  string[][] $record
     *
     * @return array
     */
    public function __invoke(array $record)
    {
        $record['context']['pid'] = \getmypid();
        $record['context']['uid'] = \getmyuid();

        return $record;
    }
}
