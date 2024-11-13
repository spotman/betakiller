<?php

declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\DotEnvWriter;
use BetaKiller\Env\AppEnvInterface;
use Psr\Log\LoggerInterface;

class StoreAppRevision extends AbstractTask
{
    private const ARG_PATH     = 'path';
    private const ARG_REVISION = 'revision';

    /**
     * StoreAppRevision constructor.
     *
     * @param \BetaKiller\DotEnvWriter $writer
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        private readonly DotEnvWriter $writer,
        private readonly LoggerInterface $logger
    ) {
    }

    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [
            $builder->string(self::ARG_PATH)->required()->label('Path to .env file'),
            $builder->string(self::ARG_REVISION)->required()->label('Revision key'),
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $path     = $params->getString(self::ARG_PATH);
        $revision = $params->getString(self::ARG_REVISION);

        if (!file_exists($path)) {
            throw new TaskException('Missing .env file at ":path"', [
                ':path' => $path,
            ]);
        }

        $this->writer->update($path, [
            AppEnvInterface::APP_REVISION => $revision,
        ]);

        $this->logger->info('Revision set to :value', [
            ':value' => $revision,
        ]);
    }
}
