<?php

use BetaKiller\Task\TaskException;

class Task_StoreAppRevision extends \BetaKiller\Task\AbstractTask
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\AppEnv
     */
    private $appEnv;

    /**
     * @Inject
     * @var \Spotman\DotEnv\DotEnv
     */
    private $dotEnv;

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

        $dotEnvFile = $this->appEnv->getAppRoot().DIRECTORY_SEPARATOR.'.env';

        $this->dotEnv->update($dotEnvFile, [
            'APP_REVISION' => $revision,
        ]);

        $this->logger->debug('Revision set to :value', [':value' => $revision]);
    }
}
