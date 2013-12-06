<?php defined('SYSPATH') OR die('No direct script access.');

Route::set('widget-controller', 'w/<widget>(/<action>)',
    array('widget' => '[A-Za-z_]+', 'action' => '[A-Za-z_]+'))
    ->defaults(array(
        'controller'    => 'Widget',
        'action'        => 'render',
    ));