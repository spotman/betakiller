<?php
namespace BetaKiller\Assets\Storage;

use BetaKiller\Assets\Exception\AssetsStorageException;
use BetaKiller\Env\AppEnvInterface;

final class LocalPublicAssetsStorage extends AbstractLocalAssetsStorage
{
    public const CODENAME = 'LocalPublic';

    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private $appEnv;

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
     * @param string $basePath
     *
     * @throws AssetsStorageException
     */
    public function setBasePath(string $basePath): void
    {
        $docRoot = $this->appEnv->getDocRootPath();

        parent::setBasePath($docRoot.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$basePath);
    }

    /**
     *  Returns true if files are located under document root
     *
     * @return bool
     */
    public function isInsideDocRoot(): bool
    {
        // Public files are located under docroot
        return true;
    }
}
