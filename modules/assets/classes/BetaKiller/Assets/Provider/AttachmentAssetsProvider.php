<?php
namespace BetaKiller\Assets\Provider;

use BetaKiller\Assets\Model\AssetsModelInterface;

/**
 * Class AttachmentAssetsProvider
 * Common attachment provider
 *
 * @package BetaKiller\Assets\AbstractProvider
 */
final class AttachmentAssetsProvider extends AbstractHasPreviewAssetsProvider
    implements AttachmentAssetsProviderInterface
{
    /**
     * Returns array of allowed actions` names
     *
     * @return string[]
     */
    public function getActions(): array
    {
        return [
            self::ACTION_ORIGINAL,
            self::ACTION_PREVIEW,
            self::ACTION_UPLOAD,
            self::ACTION_DOWNLOAD,
            self::ACTION_DELETE,
        ];
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     * @param string                                        $size
     *
     * @return string
     */
    public function makePreviewContent(AssetsModelInterface $model, string $size): string
    {
        // TODO Detect preview image by mime-type and return its contents
        return '';
    }
}
