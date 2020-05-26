<?php
declare(strict_types=1);

namespace BetaKiller\Assets\Provider;

use BetaKiller\Assets\Exception\AssetsModelException;
use BetaKiller\Assets\Exception\AssetsProviderException;
use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Assets\Model\HasPreviewAssetsModelInterface;
use BetaKiller\Helper\LoggerHelper;
use function count;
use function in_array;

abstract class AbstractHasPreviewAssetsProvider extends AbstractAssetsProvider implements HasPreviewProviderInterface
{
    /**
     * @param \BetaKiller\Assets\Model\HasPreviewAssetsModelInterface $model
     * @param string                                                  $size 300x200
     *
     * @return string
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     */
    public function getPreviewUrl(HasPreviewAssetsModelInterface $model, ?string $size = null): string
    {
        $size = $this->determinePreviewSize($size);

        // /assets/<providerKey>/<pathStrategy>/<action>(-<size>).<ext>
        return $this->getItemUrl('preview', $model, $size);
    }

    /**
     * @param string $size
     *
     * @return string
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     */
    protected function determinePreviewSize(?string $size): string
    {
        if (!$size) {
            $size = $this->getPreferredPreviewSize();
        }

        if (!in_array($size, $this->getAllowedPreviewSizes(), true)) {
            $size = $this->getPreferredPreviewSize();

            LoggerHelper::logRawException(
                $this->logger,
                new AssetsProviderException('Preview size ":size" is not allowed', [':size' => $size])
            );
        }

        return $size;
    }

    /**
     * Defines allowed sizes for previews
     * Returns array of strings like this
     *
     * array('300x200', '75x75', '400x', 'x250')
     *
     * @return array
     */
    public function getAllowedPreviewSizes(): array
    {
        return $this->config->getProviderConfigValue($this, [
            self::CONFIG_MODEL_PREVIEW_KEY,
            self::CONFIG_MODEL_PREVIEW_SIZES_KEY,
        ]);
    }

    /**
     * @return int
     */
    public function getPreviewQuality(): int
    {
        return (int)$this->config->getProviderConfigValue($this, [
            self::CONFIG_MODEL_PREVIEW_KEY,
            self::CONFIG_MODEL_PREVIEW_QUALITY_KEY,
        ]) ?: 80; // This is optimal for JPEG
    }

    /**
     * @return string
     */
    public function getPreferredPreviewSize(): string
    {
        $previewSizes = $this->getAllowedPreviewSizes();

        // Using first preview as a preferred one
        if (count($previewSizes) > 0) {
            return reset($previewSizes);
        }

        throw new AssetsProviderException('Can not detect preferred preview size for :provider', [
            ':provider' => $this->codename,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function isCroppedPreview(): bool
    {
        return (bool)$this->config->getProviderConfigValue($this, [
            self::CONFIG_MODEL_PREVIEW_KEY,
            self::CONFIG_MODEL_PREVIEW_CROP_KEY,
        ], true);
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string[]
     */
    public function getInfo(AssetsModelInterface $model): array
    {
        if (!$model instanceof HasPreviewAssetsModelInterface) {
            throw new AssetsModelException('Model ":name" must implement :int to get preview info', [
                ':name' => $model::getModelName(),
                ':int'  => HasPreviewAssetsModelInterface::class,
            ]);
        }

        $previews = [];

        foreach ($this->getAllowedPreviewSizes() as $previewSize) {
            $previews[$previewSize] = $this->getPreviewUrl($model, $previewSize);
        }

        $preferredSize = $this->getPreferredPreviewSize();

        return array_merge(parent::getInfo($model), [
            HasPreviewAssetsModelInterface::API_KEY_PREVIEW_URL      => $previews[$preferredSize],
            HasPreviewAssetsModelInterface::API_KEY_ALL_PREVIEWS_URL => $previews,
        ]);
    }
}
