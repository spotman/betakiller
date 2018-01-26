<?php

use BetaKiller\Task\TaskException;

class Task_StoreAppRevision extends \BetaKiller\Task\AbstractTask
{
    protected function defineOptions(): array
    {
        return [
            'revision' => null,
        ];
    }

    /**
     * @param array $params
     *
     * @throws \BetaKiller\Task\TaskException
     */
    protected function _execute(array $params): void
    {
        $revision = $this->getOption('revision');

        if (!$revision) {
            throw new TaskException('Missing revision number');
        }

        // TODO Save it in the .env app file

        // TODO Create \BetaKiller\Env\DotEnv wrapper for phpdotenv package + setter for keys

        $this->logger->info('Revision set to :value', [':value' => $revision]);
    }
}
