<?php

use BetaKiller\Assets\Model\AssetsModelImageInterface;

/**
 * Uploading/downloading/deleting files via concrete provider
 *
 * "assets/upload/<provider>"
 */
Route::set('assets-provider-upload', 'assets/<provider>/upload')
    ->defaults([
        'module'     => 'assets',
        'controller' => 'Assets',
        'action'     => 'upload',
    ]);

$assetsExtensionRegexp = '[a-z]{2,}'; // (jpg|jpeg|gif|png)
$assetsSizeRegexp      = '[0-9]{0,3}'.AssetsModelImageInterface::SIZE_DELIMITER.'[0-9]{0,3}';

/**
 * Deploy/delete/preview files via concrete provider
 */
Route::set('assets-provider-item', 'assets/<provider>/<item_url>/<action>(.<ext>)', [
    'item_url' => '[A-Za-z0-9\/]+',
    'action'   => '(original|download|delete)',
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
    'action'   => 'preview',
    'size'     => $assetsSizeRegexp,
    'ext'      => $assetsExtensionRegexp,
])
    ->defaults([
        'module'     => 'assets',
        'controller' => 'Assets',
    ]);

unset($assetsExtensionRegexp, $assetsSizeRegexp);
