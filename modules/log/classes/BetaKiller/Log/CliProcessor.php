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
        global $argv;

        $record['context']['pid'] = \getmypid();
        $record['context']['uid'] = \getmyuid();
        $record['context']['cwd'] = implode(' ', $argv ?: []);

        return $record;
    }
}
