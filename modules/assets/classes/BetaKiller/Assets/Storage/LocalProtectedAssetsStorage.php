<?php
namespace BetaKiller\Assets\Storage;

use BetaKiller\Assets\AssetsStorageException;
use BetaKiller\Helper\AppEnvInterface;

class LocalProtectedAssetsStorage extends AbstractLocalAssetsStorage
{
    public const CODENAME = 'Protected';

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * AbstractLocalAssetsStorage constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     */
    public function __construct(AppEnvInterface $appEnv)
    {
        $this->appEnv = $appEnv;
    }

    /**
     * @param string $basePath
     *
     * @throws AssetsStorageException
     */
    public function setBasePath(string $basePath): void
    {
        $appRoot = $this->appEnv->getAppRootPath();

        parent::setBasePath($appRoot.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$basePath);
    }

    /**
     * Returns true if storage`s files are located outside of document root and deploy is needed
     *
     * @return bool
     */
    public function isDeployRequired(): bool
    {
        // Public files are located outside of the docroot
        return true;
    }
}
