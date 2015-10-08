<?php defined('SYSPATH') OR die('No direct script access.');

// Interactive API console
Route::set('console', 'console')
    ->defaults(array(
        'module'        => 'console',
        'controller'    => 'Console',
        'action'        => 'show',
    ));
