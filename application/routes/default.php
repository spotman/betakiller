<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */

// TODO

Route::set('login', 'login')
    ->defaults(array(
        'module'        => 'auth',
//        'directory'     => 'Planet',
        'controller'    => 'Auth',
        'action'        => 'login',
    ));

Route::set('logout', 'logout')
    ->defaults(array(
        'module'        => 'auth',
//        'directory'     => 'Planet',
        'controller'    => 'Auth',
        'action'        => 'logout',
    ));

//Route::set('file-actions', 'file/<action>/<id>)', array('id' => '[0-9]+'))
//    ->defaults(array(
//        'controller' => 'File',
//));

//Route::set('media', 'media(/<file>)', array('file' => '.+'))
//    ->defaults(array(
//    'file' => NULL,
//));

//Route::set('default', '(<controller>(/<action>(/<id>)))')
//    ->defaults(array(
//        'controller' => 'default',
//        'action'     => 'index',
//    ));