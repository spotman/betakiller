<?php

/**
 * The directory in which your application specific resources are located.
 * The application directory must contain the bootstrap.php file.
 *
 * @link http://kohanaframework.org/guide/about.install#application
 */
$application = 'application';

/**
 * The directory in which your modules are located.
 *
 * @link http://kohanaframework.org/guide/about.install#modules
 */
$modules = 'modules';

/**
 * The directory in which the Kohana resources are located. The system
 * directory must contain the classes/kohana.php file.
 *
 * @link http://kohanaframework.org/guide/about.install#system
 */
$system = 'system';

/**
 * The default extension of resource files. If you change this, all resources
 * must be renamed to use the new extension.
 *
 * @link http://kohanaframework.org/guide/about.install#ext
 */
define('EXT', '.php');

/**
 * Set the PHP error reporting level. If you set this in php.ini, you remove this.
 * @link http://www.php.net/manual/errorfunc.configuration#ini.error-reporting
 *
 * When developing your application, it is highly recommended to enable notices
 * and strict warnings. Enable them by using: E_ALL | E_STRICT
 *
 * In a production environment, it is safe to ignore notices and strict warnings.
 * Disable them by using: E_ALL ^ E_NOTICE
 *
 * When using a legacy application with PHP >= 5.3, it is recommended to disable
 * deprecated notices. Disable with: E_ALL & ~E_DEPRECATED
 */
error_reporting(E_ALL | E_STRICT);

/**
 * End of standard configuration! Changing any of the code below should only be
 * attempted by those with a working knowledge of Kohana internals.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 */

// Set the full path to the docroot
define('DOCROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

// Make the application relative to the docroot, for symlink'd index.php
$application = realpath(DOCROOT.$application);

if (!is_dir($application))
    die('Application directory is not exists');

// Make the modules relative to the docroot, for symlink'd index.php
$modules = realpath(DOCROOT.$modules);

if (!is_dir($modules))
    die('Core modules directory is not exists');

// Make the system relative to the docroot, for symlink'd index.php
$system = realpath(DOCROOT.$system);

if (!is_dir($system))
    die('System directory is not exists');

// Define the absolute paths for configured directories
define('APPPATH', $application.DIRECTORY_SEPARATOR);
define('MODPATH', $modules.DIRECTORY_SEPARATOR);
define('SYSPATH', $system.DIRECTORY_SEPARATOR);

// Clean up the configuration vars
unset($application, $modules, $system);

//if (file_exists('install'.EXT))
//{
//    // Load the installation check
//    return include 'install'.EXT;
//}

/**
 * Define the start time of the application, used for profiling.
 */
if ( ! defined('KOHANA_START_TIME'))
{
    define('KOHANA_START_TIME', microtime(TRUE));
}

/**
 * Define the memory usage at the start of the application, used for profiling.
 */
if ( ! defined('KOHANA_START_MEMORY'))
{
    define('KOHANA_START_MEMORY', memory_get_usage());
}

try
{
    // Bootstrap the application
    require APPPATH.'bootstrap.php';
}
catch (Exception $e)
{
    @ ob_end_clean();

    http_response_code(500);

    if (class_exists('Kohana'))
    {
        $in_dev = in_array(Kohana::$environment, [Kohana::DEVELOPMENT, Kohana::TESTING]);
    }
    else
    {
        $in_dev = FALSE;
    }

    $message = $e->getMessage().PHP_EOL.PHP_EOL.$e->getTraceAsString();
    if ($in_dev)
    {
        // Show to dev
        echo (PHP_SAPI == 'cli') ? $message : nl2br($message);
    }
    else
    {
        // Write to default log
        error_log($message);
    }

    return;
}


if (PHP_SAPI == 'cli') // Try and load minion
{
    class_exists('Minion_Task') OR die('Please enable the Minion module for CLI support.');
    set_exception_handler(array('Minion_Exception', 'handler'));

    Log::debug('Running :name env', [':name' => Kohana::$environment_string]);

    Minion_Task::factory(Minion_CLI::options())->execute();
}
else
{
    /**
     * Execute the main request. A source of the URI can be passed, eg: $_SERVER['PATH_INFO'].
     * If no source is specified, the URI will be automatically detected.
     */
    echo Request::factory(TRUE, array(), FALSE)
        ->execute()
        ->send_headers() // Allow multiple "Link" headers for HTTP2 Server Push feature
        ->body();
}
