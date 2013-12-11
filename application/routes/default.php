<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */

Route::set('widget-controller', 'w/<widget>(/<action>)',
    array('widget' => '[A-Za-z_]+', 'action' => '[A-Za-z_]+'))
    ->defaults(array(
        'controller'    => 'Widget',
        'action'        => 'render',
    ));

// Make it last
Route::set('default-iface-controller', '(<uri>)', array('uri' => '.+'))
    ->defaults(array(
        'controller'    => 'IFace',
        'action'        => 'render',
    ));

//Route::set('login', 'login')
//    ->defaults(array(
//        'module'        => 'auth',
//        'controller'    => 'Auth',
//        'action'        => 'login',
//    ));
//
//Route::set('logout', 'logout')
//    ->defaults(array(
//        'module'        => 'auth',
//        'controller'    => 'Auth',
//        'action'        => 'logout',
//    ));
//
