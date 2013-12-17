<?php defined('SYSPATH') or die('No direct script access.');

// получаем настройки
// $config_file = Kohana::$config->load($this->config_name);
// $this->config += $config_file->$config_key;

$ksf_config = Kohana::$config->load("staticfiles");
$static_url = trim($ksf_config['url'], '/');
$cc_url = trim($ksf_config['clear_cache_url'], '/');

Route::set( 'kohana-static-files', $static_url.'/<file>', array('file'=>'.*') )
    ->defaults(array(
	'controller' => 'staticfiles',
	'action' => 'index'
	));

Route::set( 'kohana-static-files-clear', $cc_url )
    ->defaults(array(
    'controller' => 'staticfiles',
    'action' => 'clear'
));

require_once Kohana::find_file('vendor', 'jsmin');

define('STATICFILES_HOST', $ksf_config['host']);
define('STATICFILES_PATH', $ksf_config['url']);
// define('STATICFILES_URL', $ksf_config['host'].$ksf_config['url']);
define('STATICFILES_URL', trim($ksf_config['host'], '/') . $ksf_config['url']);