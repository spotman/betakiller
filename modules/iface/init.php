<?php defined('SYSPATH') OR die('No direct script access.');

Route::set('widget-controller', 'w/<widget>/<action>')
    ->defaults(array(
        'controller' => 'Widget'
    ));