<?php
namespace BetaKiller\Assets\Storage;

use BetaKiller\Assets\AssetsStorageException;
use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Assets\MultiLevelPath;

class LocalAssetsStorage extends AbstractAssetsStorage
{
    const CODENAME = 'Local';

    /**
     * Allow creating nested files and directories (groups/other security must be done via server umask config)
     *
     * @var int
     */
    private $dirMask = 0777;

    /**
     * Prevent executing (groups/other security must be done via server umask config)
     *
     * @var int
     */
    private $fileMask = 0666;

    /**
     * @var \BetaKiller\Assets\MultiLevelPath
     */
    private $multiLevelPath;

    /**
     * AbstractAssetsStorage constructor.
     *
     * @param \BetaKiller\Assets\MultiLevelPath $multiLevelPath
     */
    public function __construct(MultiLevelPath $multiLevelPath)
    {
        $this->multiLevelPath = $multiLevelPath;
    }

    /**
     * @param string $basePath
     *
     * @throws AssetsStorageException
     */
    public function setBasePath(string $basePath): void
    {
        if (!file_exists($basePath) || !is_dir($basePath)) {
            throw new AssetsStorageException('Incorrect path provided :value', [':value' => $basePath]);
        }

        parent::setBasePath($basePath);
    }

    protected function getModelRelativePath(AssetsModelInterface $model): string
    {
        $basePath = parent::getModelRelativePath($model);

        // Make all paths multi-level to prevent filesystem performance issues
        return $this->multiLevelPath->make($basePath);
    }

    /**
     * @param string $path
     *
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    private function checkFileExists(string $path): void
    {
        if (!file_exists($path)) {
            throw new AssetsStorageException('File :path does not exists', [':path' => $path]);
        }
    }

    /**
     * Returns content of the file
     *
     * @param string $path Local path in storage
     *
     * @return string
     * @throws AssetsStorageException
     */
    protected function doGet(string $path): string
    {
        $this->checkFileExists($path);

        return file_get_contents($path);
    }

    /**
     * Creates the file or updates its content
     *
     * @param string $path    Local path to file in current storage
     * @param string $content String content of the file
     *
     * @throws AssetsStorageException
     */
    protected function doPut(string $path, string $content): void
    {
        $baseDir = dirname($path);

        if (!file_exists($baseDir) && !@mkdir($baseDir, $this->dirMask, true) && !is_dir($baseDir)) {
            throw new AssetsStorageException('Can not create path :dir', [':dir' => $baseDir]);
        }

        file_put_contents($path, $content);

        chmod($path, $this->fileMask);
    }

    /**
     * Deletes file
     *
     * @param string $path Local path
     *
     * @return bool
     * @throws AssetsStorageException
     */
    protected function doDelete(string $path): bool
    {
        $this->checkFileExists($path);

        return unlink($path);
    }
}
