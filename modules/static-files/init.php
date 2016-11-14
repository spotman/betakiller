<?php defined('SYSPATH') or die('No direct script access.');

$ksf = StaticFile::instance();

$static_url = trim($ksf->get_base_url(), '/');
$static_cache_url = trim($ksf->get_cache_base_url(), '/');
$clear_static_cache_url = trim($ksf->get_clear_cache_url(), '/');

Route::set( 'kohana-static-files', $static_url.'/<file>', array('file'=>'.*') )
    ->defaults(array(
	'controller' => 'StaticFiles',
	'action' => 'index'
	));

Route::set( 'kohana-static-files-clear', $clear_static_cache_url )
    ->defaults(array(
    'controller' => 'StaticFiles',
    'action' => 'clear'
));

Route::set( 'kohana-static-files-missing-cache', $static_cache_url.'/<file>', array('file'=>'.*') )
    ->defaults(array(
    'controller' => 'StaticFiles',
    'action' => 'missing'
));

require_once Kohana::find_file('vendor', 'jsmin');

unset($ksf, $static_url, $static_cache_url, $clear_static_cache_url);
