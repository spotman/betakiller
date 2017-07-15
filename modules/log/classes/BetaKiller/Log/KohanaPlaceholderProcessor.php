<?php
namespace BetaKiller\Log;

class KohanaPlaceholderProcessor
{
    /**
     * @param  string[][] $record
     * @return array
     */
    public function __invoke(array $record)
    {
        if ($record['context'] ?? null) {
            $data = [];

            foreach ($record['context'] as $key => $item) {
                if (is_string($key) && is_scalar($item) && $key[0] === ':') {
                    $data[$key] = (string)$item;
                    unset($record['context'][$key]);
                }
            }

            $record['message'] = strtr($record['message'], $data);
        }

        return $record;
    }
}
