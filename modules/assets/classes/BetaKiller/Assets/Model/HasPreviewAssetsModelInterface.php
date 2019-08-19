<?php
namespace BetaKiller\Assets\Model;

interface HasPreviewAssetsModelInterface extends AssetsModelInterface
{
    public const SIZE_ORIGINAL = 'original';
    public const SIZE_PREVIEW  = 'preview';

    // Dimensions values delimiter
    public const SIZE_DELIMITER = 'x';

    public const API_KEY_PREVIEW_URL      = 'preview';
    public const API_KEY_ALL_PREVIEWS_URL = 'previews';
}
