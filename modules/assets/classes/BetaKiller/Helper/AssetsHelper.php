<?php
namespace BetaKiller\Helper;

use BetaKiller\Assets\AssetsException;
use BetaKiller\Assets\Model\AssetsModelImageInterface;
use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Assets\Provider\ImageAssetsProviderInterface;

class AssetsHelper
{
    /**
     * @Inject
     * @var \BetaKiller\Assets\AssetsProviderFactory
     */
    private $providerFactory;

    /**
     * Returns URL for uploading new assets
     *
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string
     */
    public function getUploadUrl(AssetsModelInterface $model): string
    {
        return $this->getProviderByModel($model)->getUploadUrl();
    }

    /**
     * Returns URL to original file/image
     *
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string
     */
    public function getOriginalUrl(AssetsModelInterface $model): string
    {
        return $this->getProviderByModel($model)->getOriginalUrl($model);
    }

    public function getPreviewUrl(AssetsModelImageInterface $model, ?string $size = null): string
    {
        return $this->getImageProviderByModel($model)->getPreviewUrl($model, $size);
    }

    public function getDownloadUrl(AssetsModelInterface $model): string
    {
        return $this->getProviderByModel($model)->getDownloadUrl($model);
    }

    public function getAttributesForImgTag(AssetsModelImageInterface $model, $size, array $attributes = null): array
    {
        return $this->getImageProviderByModel($model)->getAttributesForImgTag($model, $size, $attributes);
    }

    private function getProviderByModel(AssetsModelInterface $model): AssetsProviderInterface
    {
        $name = $model->getModelName();

        return $this->providerFactory->createFromModelCodename($name);
    }

    private function getImageProviderByModel(AssetsModelImageInterface $model): ImageAssetsProviderInterface
    {
        $provider = $this->getProviderByModel($model);

        if (!($provider instanceof ImageAssetsProviderInterface)) {
            throw new AssetsException('Model :name must be linked to image provider',
                [':name' => $model->getModelName()]);
        }

        return $provider;
    }
}
