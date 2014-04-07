<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Uploading/downloading files to concrete provider
 *
 * "assets/<provider>/upload"
 * "assets/<provider>/download"
 */
Route::set('assets-provider-action', 'assets/<provider>/<action>', array('action' => '(upload|download)'))
    ->defaults(array(
        'module'        => 'assets',
        'controller'    => 'Assets',
    ));
