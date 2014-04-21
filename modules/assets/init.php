<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Uploading/downloading/deleting files via concrete provider
 *
 * "assets/upload/<provider>"
 * "assets/public/<provider>/<hash>"
 */
Route::set('assets-provider-upload', 'assets/<provider>/upload')
    ->defaults(array(
        'module'        => 'assets',
        'controller'    => 'Assets',
        'action'        => 'upload',
    ));

Route::set('assets-provider-item', 'assets/<provider>/<hash>/<action>', array('action' => '(public|delete|preview)'))
    ->defaults(array(
        'module'        => 'assets',
        'controller'    => 'Assets',
    ));
