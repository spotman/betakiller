<?php

declare(strict_types=1);

namespace BetaKiller\Task\Assets;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Console\ConsoleTaskInterface;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Task\TaskException;
use Kohana;
use Psr\Log\LoggerInterface;
use RuntimeException;

readonly class Deploy implements ConsoleTaskInterface
{
    private const ARG_TARGET = 'target';

    public function __construct(private AppEnvInterface $appEnv, private LoggerInterface $logger)
    {
    }

    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [
            $builder->string(self::ARG_TARGET)->optional('assets'.DIRECTORY_SEPARATOR.'static'),
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $relativeDir = $params->getString(self::ARG_TARGET);
        $targetDir   = $this->appEnv->getDocRootPath().DIRECTORY_SEPARATOR.$relativeDir;

        $this->logger->debug('Build target dir is :dir', [
            ':dir' => $targetDir,
        ]);

        if (!is_writable($targetDir)) {
            throw new TaskException('Target directory is not writable: :path', [
                ':path' => $targetDir,
            ]);
        }

        $staticFilesList = Kohana::list_files('assets'.DIRECTORY_SEPARATOR.'static');

        foreach ($staticFilesList as $listItem) {
            $this->processListItem($listItem, $targetDir);
        }
    }

    protected function processListItem(string|array $listItem, string $targetDir): void
    {
        if (is_array($listItem)) {
            foreach ($listItem as $item) {
                $this->processListItem($item, $targetDir);
            }
        } else {
            $this->processFile($listItem, $targetDir);
        }
    }

    /**
     * @param array|string $original
     * @param string       $targetBase
     *
     * @throws \BetaKiller\Task\TaskException
     * @throws \RuntimeException
     * @throws \Kohana_Exception
     */
    protected function processFile(string $original, string $targetBase): void
    {
        $fileArray = explode('static', $original, 2);

        $target = $targetBase.$fileArray[1];

        // Skip existing
        if (file_exists($target)) {
            return;
        }

        $targetBaseDir = dirname($target);

        if (!file_exists($targetBaseDir) && !mkdir($targetBaseDir, 0777, true) && !is_dir($targetBaseDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $targetBaseDir));
        }

        $deployed = @symlink($original, $target);

        if (!$deployed) {
            throw new TaskException('Can not deploy file :original to :target', [
                ':original' => $original,
                ':target'   => $target,
            ]);
        }

        $this->logger->debug('Symlink created from :from to :to', [
            ':from' => $original,
            ':to'   => $target,
        ]);
    }
}
