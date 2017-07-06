<?php
namespace BetaKiller\Assets\Provider;

use BetaKiller\Assets\Model\AssetsModelImageInterface;
use BetaKiller\Assets\Model\AssetsModelInterface;

interface ImageAssetsProviderInterface extends AssetsProviderInterface
{
    /**
     * @param AssetsModelInterface $model
     * @param string               $size 300x200
     *
     * @return string
     * @throws \BetaKiller\Assets\AssetsProviderException
     */
    public function getPreviewUrl(AssetsModelInterface $model, ?string $size = null): string;

    public function makePreviewContent(AssetsModelImageInterface $model, string $size): string;

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelImageInterface $model
     * @param null|string                                        $size
     * @param array|null                                         $attrs
     *
     * @return array
     */
    public function getAttributesForImgTag(AssetsModelImageInterface $model, ?string $size = null, array $attrs = null): array;

    /**
     * @return int
     */
    public function getUploadMaxHeight(): ?int;

    /**
     * @return int
     */
    public function getUploadMaxWidth(): ?int;

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
     * @return int
     */
    public function getPreviewQuality(): int;
}
