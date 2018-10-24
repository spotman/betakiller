<?php

// -- Environment setup --------------------------------------------------------

// Load the core Kohana class
require SYSPATH.'classes/Kohana/Core'.EXT;

if (is_file(APPPATH.'classes/Kohana'.EXT)) {
    // Application extends the core
    require APPPATH.'classes/Kohana'.EXT;
} else {
    // Load empty core extension
    require SYSPATH.'classes/Kohana'.EXT;
}

/**
 * Set the default time zone.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 * @link http://www.php.net/manual/timezones
 */
date_default_timezone_set('Europe/Moscow');

/**
 * Set the default locale.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 * @link http://www.php.net/manual/function.setlocale
 */
setlocale(LC_ALL, 'ru_RU.utf-8', 'ru');

/**
 * Enable the Kohana auto-loader.
 *
 * @link http://kohanaframework.org/guide/using.autoloading
 * @link http://www.php.net/manual/function.spl-autoload-register
 */
spl_autoload_register([\Kohana::class, 'auto_load']);


/**
 * Enable autoloading of vendor libs
 */

$vendor_autoload = DOCROOT.'vendor/autoload.php';

if (!file_exists($vendor_autoload)) {
    throw new Exception('Init Composer first');
}

require_once $vendor_autoload;

/**
 * Enable auto-loader for unserialization.
 *
 * @link http://www.php.net/manual/function.spl-autoload-call
 * @link http://www.php.net/manual/var.configuration#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

/**
 * Prevent exceptions for deep-nested calls with enabled xDebug
 * @url http://stackoverflow.com/a/4293870/3640406
 */
ini_set('xdebug.max_nesting_level', 200);

// -- Configuration and initialization -----------------------------------------

$env = getenv('APP_ENV');

// Import arguments in CLI mode
if (PHP_SAPI === 'cli') {
    // No short options
    $short_options = '';

    $long_options = [
        'stage::',    // Run CLI script in concrete stage
    ];

    $cli_options = getopt($short_options, $long_options);

    if (isset($cli_options['stage'])) {
        // Store requested stage in environment var
        $env = $cli_options['stage'];
    }
}

/**
 * Cookie salt is used to make sure cookies haven't been modified by the client
 */
Cookie::$salt = 'hd398gfhk75403lnvrfe8d10gg';

/**
 * Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Kohana::<INVALID_ENV_NAME>"
 */

if (!$env) {
    throw new Exception('Missing APP_ENV');
}

Kohana::$environmentString = strtolower($env);
Kohana::$environment       = constant('Kohana::'.strtoupper(Kohana::$environmentString));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config = new Config;
Kohana::$config->attach(new Config_File);

/**
 * Attach the environment specific configuration file reader
 */
Kohana::$config->attach(new Config_File('config/environments/'.Kohana::$environmentString));


/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - integer  cache_life  lifetime, in seconds, of items cached              60
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 * - boolean  expose      set the X-Powered-By header                        FALSE
 */
Kohana::init(Kohana::$config->load('init')->as_array());

// Attach basic log for debugging unit tests
if (PHP_SAPI === 'cli' && Kohana::$environment === Kohana::TESTING) {
    Kohana::$log->attach(new Log_StdOut(), Log::DEBUG, Log::EMERGENCY);
}

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
$modules = Kohana::$config->load('modules')->as_array();
Kohana::modules($modules);
