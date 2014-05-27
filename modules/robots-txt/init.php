<?php defined('SYSPATH') OR die('No direct script access.');

Route::set('robots-txt', 'robots.txt')
    ->defaults(array(
        'controller' => 'RobotsTXT',
        'action' => 'index'
	));