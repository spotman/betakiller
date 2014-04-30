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
Route::set('assets-provider-item', 'assets/<provider>/<item_url>/<action>')
    ->defaults(array(
        'module'        => 'assets',
        'controller'    => 'Assets',
    ));

/**
 * Fake route for getting asset`s deployment directory
 * It`ll newer triggered because of existing real deployment directory (and default .htaccess policy also)
 */
Route::set('assets-provider-item-deploy-directory', 'assets/<provider>/<item_url>',
    array('ab' => '[A-Za-z0-9]{2}'))
    ->defaults(array(
        'module'        => 'assets',
        'controller'    => 'Assets',
    ));
