<?php
declare(strict_types=1);

namespace BetaKiller\Assets;

use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Helper\AppEnvInterface;

class AssetsDeploymentService
{
    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * AssetsDeploymentService constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     */
    public function __construct(AppEnvInterface $appEnv)
    {
        $this->appEnv = $appEnv;
    }

    /**
     * @param \BetaKiller\Assets\Provider\AssetsProviderInterface $provider
     * @param \BetaKiller\Assets\Model\AssetsModelInterface       $model
     * @param string                                              $content
     * @param string                                              $action
     * @param null|string                                         $suffix
     *
     * @return bool
     * @throws \BetaKiller\Assets\AssetsProviderException
     */
    public function deploy(
        AssetsProviderInterface $provider,
        AssetsModelInterface $model,
        string $content,
        string $action,
        ?string $suffix = null
    ): bool {
        if (!$this->isDeploymentEnabled($provider)) {
            return false;
        }

        // Get item base deploy path
        $fullPath = $this->getItemDeployFullPath($provider, $model, $action, $suffix);
        $path     = \dirname($fullPath);

        // Create deploy path if not exists
        if (!file_exists($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
            throw new AssetsProviderException('Can not create path :value', [
                ':value' => $path,
            ]);
        }

        file_put_contents($fullPath, $content);

        // Update last modification time for better caching
        $lastModified = $model->getLastModifiedAt() ?: new \DateTimeImmutable();
        touch($fullPath, $lastModified->getTimestamp());

        return true;
    }

    /**
     * Removes all deployed versions of provided asset
     *
     * @param \BetaKiller\Assets\Provider\AssetsProviderInterface $provider
     * @param \BetaKiller\Assets\Model\AssetsModelInterface       $model
     */
    public function clear(AssetsProviderInterface $provider, AssetsModelInterface $model): void
    {
        if (!$this->isDeploymentEnabled($provider)) {
            return;
        }

        $path = $this->getItemDeployFullPath($provider, $model);

        if (!file_exists($path)) {
            return;
        }

        // Remove all versions of file
        foreach (glob($path.DIRECTORY_SEPARATOR.'*') as $file) {
            unlink($file);
        }

        // Remove directory itself
        rmdir($path);
    }

    private function isDeploymentEnabled(AssetsProviderInterface $provider): bool
    {
        // No deployment in dev mode
        return $provider->isDeploymentNeeded();
    }

    /**
     * Returns asset`s base deploy directory
     *
     * @param \BetaKiller\Assets\Provider\AssetsProviderInterface $provider
     * @param AssetsModelInterface                                $model
     *
     * @param string                                              $action
     * @param null|string                                         $suffix
     *
     * @return string
     */
    private function getItemDeployFullPath(
        AssetsProviderInterface $provider,
        AssetsModelInterface $model,
        ?string $action = null,
        ?string $suffix = null
    ): string {
        $action = $action ?? AssetsProviderInterface::ACTION_ORIGINAL;

        $path = $provider->getDeployRelativePath($model, $action, $suffix);

        return $this->appEnv->getDocRootPath().DIRECTORY_SEPARATOR.$path;
    }
}
