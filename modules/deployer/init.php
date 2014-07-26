<?php defined('SYSPATH') OR die('No direct script access.');

Route::set('deployer', 'deployer')
    ->defaults(array(
        'module'        => 'deployer',
        'controller'    => 'Deployer',
        'action'        => 'index'
	));

Route::set('deployer-execute-command', 'deployer/<command>', array('command' => '[a-z- :]+'))
    ->defaults(array(
        'module'        => 'deployer',
        'controller'    => 'Deployer',
        'action'        => 'execute',
	));