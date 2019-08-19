<?php
declare(strict_types=1);

namespace BetaKiller\Assets\Provider;

use BetaKiller\Assets\Model\HasPreviewAssetsModelInterface;

interface HasPreviewProviderInterface extends AssetsProviderInterface
{
    public const ACTION_PREVIEW = 'preview';

    public const CONFIG_MODEL_PREVIEW_KEY         = 'preview';
    public const CONFIG_MODEL_PREVIEW_SIZES_KEY   = 'sizes';
    public const CONFIG_MODEL_PREVIEW_QUALITY_KEY = 'quality';

    /**
     * @param \BetaKiller\Assets\Model\HasPreviewAssetsModelInterface $model
     * @param string                                                  $size 300x200
     *
     * @return string
     */
    public function getPreviewUrl(HasPreviewAssetsModelInterface $model, ?string $size = null): string;

    /**
     * @param \BetaKiller\Assets\Model\HasPreviewAssetsModelInterface $model
     * @param string                                                  $size
     *
     * @return string
     */
    public function makePreviewContent(HasPreviewAssetsModelInterface $model, string $size): string;

    /**
     * Defines allowed sizes for previews
     * Returns array of strings like this
     *
     * array('300x200', '75x75', '400x', 'x250')
     *
     * @return array
     */
    public function getAllowedPreviewSizes(): array;

    /**
     * @return string
     */
    public function getPreferredPreviewSize(): string;

    /**
     * @return int
     */
    public function getPreviewQuality(): int;
}
