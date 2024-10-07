<?php

declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Env\AppEnvInterface;
use Psr\Log\LoggerInterface;
use Spotman\DotEnv\DotEnv;

class StoreAppRevision extends AbstractTask
{
    private const ARG_REVISION = 'revision';

    /**
     * @var \BetaKiller\Env\AppEnvInterface
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
     * @param \BetaKiller\Env\AppEnvInterface $appEnv
     * @param \Spotman\DotEnv\DotEnv          $dotEnv
     * @param \Psr\Log\LoggerInterface        $logger
     */
    public function __construct(AppEnvInterface $appEnv, DotEnv $dotEnv, LoggerInterface $logger)
    {
        $this->appEnv = $appEnv;
        $this->dotEnv = $dotEnv;
        $this->logger = $logger;
    }

    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [
            $builder->string(self::ARG_REVISION)->required()->label('Revision key'),
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $revision = $params->getString(self::ARG_REVISION);

        $dotEnvFile = $this->appEnv->getAppRootPath().DIRECTORY_SEPARATOR.'.env';

        // Create empty .env file if not exists
        if (!file_exists($dotEnvFile)) {
            touch($dotEnvFile);
        }

        $this->dotEnv->update($dotEnvFile, [
            AppEnvInterface::APP_REVISION => $revision,
        ]);

        $this->logger->debug('Revision set to :value', [':value' => $revision]);
    }
}
