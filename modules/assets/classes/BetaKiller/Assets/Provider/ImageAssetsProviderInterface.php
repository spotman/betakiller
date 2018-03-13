<?php
namespace BetaKiller\Assets\Provider;

use BetaKiller\Assets\Model\AssetsModelImageInterface;

interface ImageAssetsProviderInterface extends HasPreviewProviderInterface
{
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
}
