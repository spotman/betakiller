<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Uploading/downloading/deleting files via concrete provider
 *
 * "assets/upload/<provider>"
 * "assets/public/<provider>/<hash>"
 */
Route::set('assets-provider-action', 'assets/<action>/<provider>(/<hash>)', array('action' => '(upload|public|delete)'))
    ->defaults(array(
        'module'        => 'assets',
        'controller'    => 'Assets',
    ));
