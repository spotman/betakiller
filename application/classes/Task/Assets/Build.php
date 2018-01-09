<?php


class Task_Assets_Build extends Minion_Task
{
    protected function defineOptions(): array
    {
        return [
            'target' => null,
        ];
    }

    /**
     * @param array $params
     *
     * @throws \RuntimeException
     * @throws \Kohana_Exception
     */
    protected function _execute(array $params): void
    {
        $staticFilesList = Kohana::list_files('static-files');

        $targetDir = implode(DIRECTORY_SEPARATOR, [MultiSite::instance()->getSitePath(), 'builds', 'merge']);

        $this->logger->debug('Build target dir is :dir', [':dir' => $targetDir]);

        foreach ($staticFilesList as $file) {
            $this->processFile($file, $targetDir);
        }
    }

    /**
     * @param        $file
     * @param string $targetBase
     *
     * @throws \RuntimeException
     * @throws \Kohana_Exception
     */
    protected function processFile($file, string $targetBase)
    {
        if (is_array($file)) {
            foreach ($file as $item) {
                $this->processFile($item, $targetBase);
            }
        } else {
            $fileArray = explode('static-files', $file);

            $target = $targetBase.$fileArray[1];

            $targetBaseDir = dirname($target);

            if (!file_exists($targetBaseDir) && !mkdir($targetBaseDir) && !is_dir($targetBaseDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $targetBaseDir));
            }

            $this->logger->debug('Copying :from to :to', [':from' => $file, ':to' => $target]);

            copy($file, $target) && unlink($target);
        }
    }
}
