<?php
declare(strict_types=1);

namespace BetaKiller\Task\Assets;

use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;
use Kohana;
use Psr\Log\LoggerInterface;

class Deploy extends AbstractTask
{
    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Deploy constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     * @param \Psr\Log\LoggerInterface           $logger
     */
    public function __construct(AppEnvInterface $appEnv, LoggerInterface $logger)
    {
        $this->appEnv = $appEnv;
        $this->logger = $logger;

        parent::__construct();
    }

    public function defineOptions(): array
    {
        return [
            'target' => null,
        ];
    }

    public function run(): void
    {
        $staticFilesList = Kohana::list_files('static-files');

        $relativeDir = $this->getOption('target', false) ?: 'assets'.DIRECTORY_SEPARATOR.'static';
        $targetDir   = $this->appEnv->getDocRootPath().DIRECTORY_SEPARATOR.$relativeDir;

        $this->logger->debug('Build target dir is :dir', [':dir' => $targetDir]);

        foreach ($staticFilesList as $file) {
            $this->processFile($file, $targetDir);
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
    protected function processFile($original, string $targetBase): void
    {
        if (\is_array($original)) {
            foreach ($original as $item) {
                $this->processFile($item, $targetBase);
            }
        } else {
            $fileArray = explode('static-files', $original);

            $target = $targetBase.$fileArray[1];

            $targetBaseDir = \dirname($target);

            if (!file_exists($targetBaseDir) && !mkdir($targetBaseDir, 0777, true) && !is_dir($targetBaseDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $targetBaseDir));
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
}
