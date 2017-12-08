<?php

use BetaKiller\Task\AbstractTask;

class Task_Sleep extends AbstractTask
{
    protected function defineOptions(): array
    {
        return [
            'seconds' => 3,
        ];
    }

    protected function _execute(array $params): void
    {
        $seconds = (int)$params['seconds'];

        for($i = 0; $i < $seconds; $i++) {
            sleep(1);
            $this->logger->info('Done for :value seconds', [':value' => $i+1]);
        }
    }
}
