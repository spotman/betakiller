<?php
declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Helper\AppEnvInterface;
use Psr\Log\LoggerInterface;
use Spotman\DotEnv\DotEnv;

class StoreAppRevision extends AbstractTask
{
    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \Spotman\DotEnv\DotEnv
     */
    private $dotEnv;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * StoreAppRevision constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     * @param \Spotman\DotEnv\DotEnv             $dotEnv
     * @param \Psr\Log\LoggerInterface           $logger
     */
    public function __construct(AppEnvInterface $appEnv, DotEnv $dotEnv, LoggerInterface $logger)
    {
        $this->appEnv = $appEnv;
        $this->dotEnv = $dotEnv;
        $this->logger = $logger;

        parent::__construct();
    }

    public function defineOptions(): array
    {
        return [
            'revision' => null,
        ];
    }

    public function run(): void
    {
        $revision = $this->getOption('revision');

        if (!$revision) {
            throw new TaskException('Missing revision number');
        }

        $dotEnvFile = $this->appEnv->getAppRootPath().DIRECTORY_SEPARATOR.'.env';

        $this->dotEnv->update($dotEnvFile, [
            AppEnvInterface::APP_REVISION => $revision,
        ]);

        $this->logger->debug('Revision set to :value', [':value' => $revision]);
    }
}
