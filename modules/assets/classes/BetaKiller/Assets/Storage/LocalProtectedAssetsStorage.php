<?php
namespace BetaKiller\Assets\Storage;

use BetaKiller\Assets\Exception\AssetsStorageException;
use BetaKiller\Env\AppEnvInterface;

final class LocalProtectedAssetsStorage extends AbstractLocalAssetsStorage
{
    public const CODENAME = 'LocalProtected';

    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * AbstractLocalAssetsStorage constructor.
     *
     * @param \BetaKiller\Env\AppEnvInterface $appEnv
     */
    public function __construct(AppEnvInterface $appEnv)
    {
        $this->appEnv = $appEnv;
    }

    /**
     *  Returns true if files are located under document root
     *
     * @return bool
     */
    public function isInsideDocRoot(): bool
    {
        // Protected assets are located outside of document root
        return false;
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
}
