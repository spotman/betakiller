<?php


use BetaKiller\Task\AbstractTask;

class Task_Assets_Deploy extends AbstractTask
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    protected function defineOptions(): array
    {
        return [
            'target' => null,
        ];
    }

    /**
     * @param array $params
     *
     * @throws \BetaKiller\Task\TaskException
     * @throws \RuntimeException
     * @throws \Kohana_Exception
     */
    protected function _execute(array $params): void
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
     * @param array|string $file
     * @param string       $targetBase
     *
     * @throws \RuntimeException
     * @throws \Kohana_Exception
     */
    protected function processFile($file, string $targetBase): void
    {
        if (is_array($file)) {
            foreach ($file as $item) {
                $this->processFile($item, $targetBase);
            }
        } else {
            $fileArray = explode('static-files', $file);

            $target = $targetBase.$fileArray[1];

            $targetBaseDir = dirname($target);

            if (!file_exists($targetBaseDir) && !mkdir($targetBaseDir, 0777, true) && !is_dir($targetBaseDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $targetBaseDir));
            }

            $this->logger->debug('Copying to :to', [':to' => $target]);

            copy($file, $target);
        }
    }
}
