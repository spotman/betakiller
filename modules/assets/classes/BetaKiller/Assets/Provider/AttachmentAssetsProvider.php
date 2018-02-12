<?php
namespace BetaKiller\Assets\Provider;

/**
 * Class AttachmentAssetsProvider
 * Common attachment provider
 *
 * @package BetaKiller\Assets\AbstractProvider
 */
final class AttachmentAssetsProvider extends AbstractAssetsProvider implements AttachmentAssetsProviderInterface
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
            self::ACTION_UPLOAD,
            self::ACTION_DOWNLOAD,
            self::ACTION_DELETE,
        ];
    }
}
