<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Uploading/downloading/deleting files via concrete provider
 *
 * "assets/upload/<provider>"
 */
Route::set('assets-provider-upload', 'assets/<provider>/upload')
    ->defaults(array(
        'module'        => 'assets',
        'controller'    => 'Assets',
        'action'        => 'upload',
    ));

/**
 * Deploy/delete/preview files via concrete provider
 */
Route::set('assets-provider-item', 'assets/<provider>/<item_url>/<action>',
    array('item_url' => '[A-Za-z0-9\/]+', 'action' => '(original|preview|delete)'))
    ->defaults(array(
        'module'        => 'assets',
        'controller'    => 'Assets',
    ));

/**
 * Make image preview
 */
Route::set('assets-provider-item-preview', 'assets/<provider>/<item_url>/preview-<size>',
    array('item_url' => '[A-Za-z0-9\/]+', 'size' => '[0-9]*x[0-9]*'))
    ->defaults(array(
        'module'        => 'assets',
        'controller'    => 'Assets',
        'action'        => 'preview',
    ));

/**
 * Crop an image
 */
Route::set('assets-provider-item-crop', 'assets/<provider>/<item_url>/crop-<size>',
    array('item_url' => '[A-Za-z0-9\/]+', 'size' => '[0-9]*x[0-9]*'))
    ->defaults(array(
        'module'        => 'assets',
        'controller'    => 'Assets',
        'action'        => 'crop',
    ));

/**
 * Fake route for getting asset`s deployment directory
 * It`ll newer triggered because of existing real deployment directory (and default .htaccess policy also)
 */
Route::set('assets-provider-item-deploy-directory', 'assets/<provider>/<item_url>')
    ->defaults(array(
        'module'        => 'assets',
        'controller'    => 'Assets',
    ));
