<?php
declare(strict_types=1);

namespace BetaKiller\Assets\Provider;

use BetaKiller\Assets\AssetsProviderException;
use BetaKiller\Assets\Model\AssetsModelInterface;

abstract class AbstractHasPreviewAssetsProvider extends AbstractAssetsProvider implements HasPreviewProviderInterface
{
    /**
     * @param AssetsModelInterface $model
     * @param string               $size 300x200
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsException
     * @throws AssetsProviderException
     */
    public function getPreviewUrl(AssetsModelInterface $model, ?string $size = null): string
    {
        $size = $this->determinePreviewSize($size);

        // /assets/<providerKey>/<pathStrategy>/<action>(-<size>).<ext>
        return $this->getItemUrl('preview', $model, $size);
    }

    /**
     * @param string $size
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsProviderException
     */
    protected function determinePreviewSize(?string $size): string
    {
        $previewSizes = $this->getAllowedPreviewSizes();

        if (!$size && \count($previewSizes) > 0) {
            $size = $previewSizes[0];
        }

        if (!$size) {
            throw new AssetsProviderException('Can not determine preview size for :provider', [
                ':provider' => $this->codename,
            ]);
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
        return $this->getAssetsProviderConfigValue([
            self::CONFIG_MODEL_PREVIEW_KEY,
            self::CONFIG_MODEL_PREVIEW_SIZES_KEY,
        ]);
    }

    /**
     * @return int
     */
    public function getPreviewQuality(): int
    {
        return (int)$this->getAssetsProviderConfigValue([
            self::CONFIG_MODEL_PREVIEW_KEY,
            self::CONFIG_MODEL_PREVIEW_QUALITY_KEY,
        ]) ?: 80; // This is optimal for JPEG
    }
    /**
     * @param $size
     *
     * @throws \BetaKiller\Assets\AssetsProviderException
     */
    protected function checkPreviewSize($size): void
    {
        $allowedSizes = $this->getAllowedPreviewSizes();

        if (!$allowedSizes || !\in_array($size, $allowedSizes, true)) {
            throw new AssetsProviderException('Preview size :size is not allowed', [':size' => $size]);
        }
    }
}
