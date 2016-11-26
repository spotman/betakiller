<?php defined('SYSPATH') OR die('No direct script access.');

Route::set('wp-content-bc', 'wp-content/<file>', ['file' => '[\S]+'])
    ->defaults(array(
        'controller' => 'Content',
        'action' => 'files_bc_redirect'
    ));
