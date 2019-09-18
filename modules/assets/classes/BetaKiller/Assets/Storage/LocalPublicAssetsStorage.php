<?php
namespace BetaKiller\Assets\Storage;

use BetaKiller\Assets\Exception\AssetsStorageException;
use BetaKiller\Helper\AppEnvInterface;

class LocalPublicAssetsStorage extends AbstractLocalAssetsStorage
{
    public const CODENAME = 'LocalPublic';

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
        $docRoot = $this->appEnv->getDocRootPath();

        parent::setBasePath($docRoot.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$basePath);
    }

    /**
     *  Returns true if files are located under document root
     *
     * @return bool
     */
    public function isPublic(): bool
    {
        // Public files are located under docroot
        return true;
    }
}
