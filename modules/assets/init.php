<?php

use BetaKiller\Assets\Model\AssetsModelImageInterface;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Assets\Provider\ImageAssetsProviderInterface;

/**
 * Uploading/downloading/deleting files via concrete provider
 *
 * "assets/upload/<provider>"
 */
Route::set('assets-provider-upload', 'assets/<provider>/'.AssetsProviderInterface::ACTION_UPLOAD)
    ->defaults([
        'module'     => 'assets',
        'controller' => 'Assets',
        'action'     => AssetsProviderInterface::ACTION_UPLOAD,
    ]);

$assetsExtensionRegexp = '[a-z]{2,}'; // (jpg|jpeg|gif|png)
$assetsSizeRegexp      = '[0-9]{0,3}'.AssetsModelImageInterface::SIZE_DELIMITER.'[0-9]{0,3}';

$itemActions = [
    AssetsProviderInterface::ACTION_ORIGINAL,
    AssetsProviderInterface::ACTION_DOWNLOAD,
    AssetsProviderInterface::ACTION_DELETE,
];

/**
 * Deploy/delete/preview files via concrete provider
 */
Route::set('assets-provider-item', 'assets/<provider>/<item_url>/<action>(.<ext>)', [
    'item_url' => '[A-Za-z0-9\/]+',
    'action'   => '('.implode('|', $itemActions).')',
    'ext'      => $assetsExtensionRegexp,
])
    ->defaults([
        'module'     => 'assets',
        'controller' => 'Assets',
    ]);

/**
 * Make image preview
 */
Route::set('assets-provider-item-preview', 'assets/<provider>/<item_url>/<action>(-<size>)(.<ext>)', [
    'item_url' => '[A-Za-z0-9\/]+',
    'action'   => ImageAssetsProviderInterface::ACTION_PREVIEW,
    'size'     => $assetsSizeRegexp,
    'ext'      => $assetsExtensionRegexp,
])
    ->defaults([
        'module'     => 'assets',
        'controller' => 'Assets',
    ]);

unset($assetsExtensionRegexp, $assetsSizeRegexp, $itemActions);
