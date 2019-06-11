<?php
declare(strict_types=1);

namespace BetaKiller\Log;

/**
 * Class MessageCleanupProcessor
 * Replace messages with several substrings to prevent infinite loops and deduplicate similar exceptions
 *
 * @package BetaKiller\Log
 */
class MessageCleanupProcessor
{
    private const KEYS = [
        'MySQL server has gone away',
        'Server shutdown in progress',
        'Error while sending QUERY packet',
    ];

    /**
     * @param string[][] $record
     *
     * @return array
     */
    public function __invoke(array $record)
    {
        $message = $record['message'];

        foreach (self::KEYS as $key) {
            if (mb_strpos($message, $key) !== false) {
                $record['message'] = $key;
                break;
            }
        }

        return $record;
    }
}
