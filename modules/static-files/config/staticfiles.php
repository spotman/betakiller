<?php defined('SYSPATH') or die('No direct script access.');

// TODO
$host = ( isset($_SERVER['HTTP_HOST']) ) ? $_SERVER['HTTP_HOST'] : '';

// Turn on the minimization and building in production environment
$in_production = in_array( Kohana::$environment, array(Kohana::PRODUCTION, Kohana::STAGING) );

$build = $in_production;
$minimize = TRUE;

return array(

    'enabled' => $in_production,

    'js' => array(

        // scripts minimization
        'min' => $minimize,

        // building all scripts in one file by types (external, inline, onload)
        'build' => $build,

        // Не билдим инлайн-скрипты, они у нас крошечные, нет смысла создавать ещё один HTTP запрос на 500 байт
        'build_inline' => FALSE,
    ),

    'css' => array(

        // styles minimization
        'min' => $minimize,

        // building all styles in one file by types (external, inline)
        'build' => $build,
    ),

    // Full path to site DOCROOT
    'path' => realpath(DOCROOT). DIRECTORY_SEPARATOR,

    // Path to copy static files that are not build in one file
    'url' => '/!/static/',

    // url for cache reset
    'clear_cache_url' => '/!/clear',

    // Path to styles and scripts builds
    'cache' => '/!/cache/',

    'chmod' => 0777,

    // Host address (base or CDN)
    // 'host' => "http://static.". $domain .".". $zone,
    'host' => "http://". $host ."/",

	// Cache reset interval
	'cache_reset_interval' => 24*60*62, // чуть более 1 дня  (чтобы кеш не генерировался в одно и то же время)

    // расширения файлов, в которых при деплое нужно произвести замену ключа {staticfiles_url} на нужное значение
    'replace_url_exts' => array(
        'css',
        'js',
        'html',
    ),

    'jquery_url' => 'https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js',
);