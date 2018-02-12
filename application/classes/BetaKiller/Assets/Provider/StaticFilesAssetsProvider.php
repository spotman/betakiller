<?php
namespace BetaKiller\Assets\Provider;

/**
 * Class StaticFilesAssetsProvider
 * Provider for serving static files
 *
 * @package BetaKiller\Assets\Provider
 */
class StaticFilesAssetsProvider extends AbstractAssetsProvider
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
            self::ACTION_DOWNLOAD,
        ];
    }
}
