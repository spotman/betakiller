<?php
namespace BetaKiller\Helper;

use BetaKiller\Assets\AssetsProviderFactory;
use BetaKiller\Assets\Exception\AssetsException;
use BetaKiller\Assets\Model\AssetsModelImageInterface;
use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Assets\Provider\HasPreviewProviderInterface;
use BetaKiller\Assets\Provider\ImageAssetsProviderInterface;

class AssetsHelper
{
    /**
     * @var \BetaKiller\Assets\AssetsProviderFactory
     */
    private $providerFactory;

    /**
     * AssetsHelper constructor.
     *
     * @param \BetaKiller\Assets\AssetsProviderFactory $providerFactory
     */
    public function __construct(AssetsProviderFactory $providerFactory)
    {
        $this->providerFactory = $providerFactory;
    }

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

    public function getPreviewUrl(AssetsModelInterface $model, ?string $size = null): string
    {
        return $this->getPreviewProviderByModel($model)->getPreviewUrl($model, $size);
    }

    public function getDownloadUrl(AssetsModelInterface $model): string
    {
        return $this->getProviderByModel($model)->getDownloadUrl($model);
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return array
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    public function getAllowedMimeTypes(AssetsModelInterface $model): array
    {
        return $this->getProviderByModel($model)->getAllowedMimeTypes();
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelImageInterface $model
     * @param                                                    $size
     * @param array|null                                         $attributes
     *
     * @return array
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    public function getAttributesForImgTag(AssetsModelImageInterface $model, $size, array $attributes = null): array
    {
        return $this->getImageProviderByModel($model)->getAttributesForImgTag($model, $size, $attributes);
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return \BetaKiller\Assets\Provider\AssetsProviderInterface
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
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
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    private function getImageProviderByModel(AssetsModelImageInterface $model): ImageAssetsProviderInterface
    {
        $provider = $this->getProviderByModel($model);

        if (!($provider instanceof ImageAssetsProviderInterface)) {
            throw new AssetsException('Model :name must be linked to image provider', [
                ':name' => $model->getModelName(),
            ]);
        }

        return $provider;
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return \BetaKiller\Assets\Provider\HasPreviewProviderInterface
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function getPreviewProviderByModel(AssetsModelInterface $model): HasPreviewProviderInterface
    {
        $provider = $this->getProviderByModel($model);

        if (!($provider instanceof HasPreviewProviderInterface)) {
            throw new AssetsException('Model :name must be linked to provider implementing :must', [
                ':name' => $model->getModelName(),
                ':must' => HasPreviewProviderInterface::class,
            ]);
        }

        return $provider;
    }
}
