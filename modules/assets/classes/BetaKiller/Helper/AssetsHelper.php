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

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelImageInterface $model
     * @param                                                    $size
     * @param array|null                                         $attributes
     *
     * @return array
     * @throws \BetaKiller\Assets\AssetsStorageException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Assets\AssetsException
     */
    public function getAttributesForImgTag(AssetsModelImageInterface $model, $size, array $attributes = null): array
    {
        return $this->getImageProviderByModel($model)->getAttributesForImgTag($model, $size, $attributes);
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return \BetaKiller\Assets\Provider\AssetsProviderInterface
     * @throws \BetaKiller\Assets\AssetsException
     * @throws \BetaKiller\Assets\AssetsStorageException
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function getProviderByModel(AssetsModelInterface $model): AssetsProviderInterface
    {
        $name = $model->getModelName();

        return $this->providerFactory->createFromModelCodename($name);
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelImageInterface $model
     *
     * @return \BetaKiller\Assets\Provider\ImageAssetsProviderInterface
     * @throws \BetaKiller\Assets\AssetsStorageException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Assets\AssetsException
     */
    private function getImageProviderByModel(AssetsModelImageInterface $model): ImageAssetsProviderInterface
    {
        $provider = $this->getProviderByModel($model);

        if (!($provider instanceof ImageAssetsProviderInterface)) {
            throw new AssetsException('Model :name must be linked to image provider',[
                ':name' => $model->getModelName(),
            ]);
        }

        return $provider;
    }
}
