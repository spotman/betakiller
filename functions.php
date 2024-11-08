<?php

declare(strict_types=1);

use BetaKiller\Config\KohanaConfigProvider;
use BetaKiller\Dev\StartupProfiler;
use BetaKiller\DI\Container;
use BetaKiller\Env\AppEnv;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Env\FakeConfigReader;
use BetaKiller\Factory\AppRunnerFactoryInterface;
use BetaKiller\ModuleInitializerInterface;
use JetBrains\PhpStorm\NoReturn;
use Psr\Container\ContainerInterface;

if (!function_exists('configureKohana')) {
    function configureKohana(): void
    {
        /**
         * Set the PHP error reporting level. If you set this in php.ini, you remove this.
         *
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

        // Always UTC for simplicity (and DATETIME type in MySQL)
        date_default_timezone_set('UTC');

        setlocale(LC_ALL, 'en_UK.UTF-8', 'en');

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

        /**
         * The default extension of resource files. If you change this, all resources
         * must be renamed to use the new extension.
         *
         * @link http://kohanaframework.org/guide/about.install#ext
         */
        define('EXT', '.php');

        // Set the full path to the docroot
        define('DOCROOT', __DIR__.DIRECTORY_SEPARATOR);

        // Make the application relative to the docroot, for symlink'd index.php
        $application = realpath(__DIR__.'/application');

        if (!is_dir($application)) {
            die('Application directory is not exists');
        }

        // Make the modules relative to the docroot, for symlink'd index.php
        $modules = realpath(__DIR__.'/modules');

        if (!is_dir($modules)) {
            die('Core modules directory is not exists');
        }

        // Make the system relative to the docroot, for symlink'd index.php
        $system = realpath(__DIR__.'/system');

        if (!is_dir($system)) {
            die('System directory is not exists');
        }

        // Define the absolute paths for configured directories
        define('APPPATH', $application.DIRECTORY_SEPARATOR);
        define('MODPATH', $modules.DIRECTORY_SEPARATOR);
        define('SYSPATH', $system.DIRECTORY_SEPARATOR);

        /**
         * Define the start time of the application, used for profiling.
         */
        if (!defined('KOHANA_START_TIME')) {
            define('KOHANA_START_TIME', microtime(true));
        }

        /**
         * Define the memory usage at the start of the application, used for profiling.
         */
        if (!defined('KOHANA_START_MEMORY')) {
            define('KOHANA_START_MEMORY', memory_get_usage());
        }

        // Load the core Kohana class
        require SYSPATH.'classes/Kohana/Core.php';

        if (is_file(APPPATH.'classes/Kohana.php')) {
            // Application extends the core
            require APPPATH.'classes/Kohana.php';
        } else {
            // Load empty core extension
            require SYSPATH.'classes/Kohana.php';
        }

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
        includeComposerAutoloader(__DIR__);
    }
}

if (!function_exists('bootstrapKohana')) {
    function bootstrapKohana(AppEnvInterface $appEnv): void
    {
        $envMode = $appEnv->getModeName();

        $p = StartupProfiler::getInstance()->start('Bootstrap Kohana');

        Kohana::$environment = constant('Kohana::'.strtoupper($envMode));

        /**
         * Attach a file reader to config. Multiple readers are supported.
         */
        Kohana::$config = new Config;
        Kohana::$config->attach(new Config_File);

        /**
         * Attach the environment specific configuration file reader
         */
        Kohana::$config->attach(new Config_File('config/environments/'.strtolower($envMode)));

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
        $initConfig = Kohana::$config->load('init')->as_array();

        Kohana::init($initConfig);

        /**
         * Enable modules. Modules are referenced by a relative or absolute path.
         */
        $coreModules = Kohana::$config->load('modules')->as_array();

        Kohana::modules($coreModules);

        StartupProfiler::getInstance()->stop($p);
    }
}

if (!function_exists('bootstrapCore')) {
    function bootstrapCore(AppEnvInterface $appEnv): ContainerInterface
    {
        registerExceptionHandler();

        $profiler = StartupProfiler::getInstance();

        $p = $profiler->start('Bootstrap Core');

        /*
        - AppEnv
        - Configure Kohana (a-la bootstrap)
        - Import app paths to Kohana CFS
        - Configure (locate config files)
        - Collect PHP-DI definitions
        - Init PHP-DI container
        - Init core modules, app modules and app itself (init.php + PHP-DI)
        */

        $appRootPath = $appEnv->isAppRunning() ? $appEnv->getAppRootPath() : null;

        // Getting site-related modules
        $appModules = $appRootPath ? getAppModules($appRootPath) : [];

        if ($appRootPath) {
            // Include Composer dependencies first (they may be used in site-related modules)
            includeComposerAutoloader($appRootPath);

            // Add site-related modules to CFS first, so it would be placed on top of core but under site app
            if ($appModules) {
                prependModulesToCfs($appModules);
            }

            // Connecting per-site directory to CFS, so it becomes top level path (it overrides /application/ and all modules)
            // Placing it after initializing modules, so it would be placed first (prepended)
            prependKohanaPath($appRootPath);

            // Repeat init after adding site-related config directory via CFS
            patchKohana();
        }

        // Instantiate config provider
        $configProvider = new KohanaConfigProvider();

        $pc = $profiler->start('Init Container');

        // Initialize container and push AppEnv and ConfigProvider into DIC
        $container = Container::factory($appEnv, $configProvider);

        $profiler->stop($pc);

        // Init core modules first
        initKohanaModules(Kohana::modules(), $container);

        if ($appRootPath) {
            // Init app-related modules if they exist
            if ($appModules) {
                initKohanaModules($appModules, $container);
            }

            // Final custom initialization
            proceedAppInitFile($appRootPath);
        }

        $profiler->stop($p);

        return $container;
    }
}

if (!function_exists('bootstrapPlatform')) {
    function bootstrapPlatform(): ContainerInterface
    {
        configureKohana();

        $appEnv = AppEnv::createFrom($_ENV, $_SERVER);

        bootstrapKohana($appEnv);

        return bootstrapCore($appEnv);
    }
}

if (!function_exists('runApp')) {
    function runApp(ContainerInterface $container): void
    {
        $p = StartupProfiler::getInstance()->start('Prepare App');

        /** @var AppRunnerFactoryInterface $runnerFactory */
        $runnerFactory = $container->get(AppRunnerFactoryInterface::class);

        $app = $runnerFactory->create();

        StartupProfiler::getInstance()->stop($p);

        $app->run();
    }
}

if (!function_exists('getAppModules')) {
    function getAppModules(string $appRootPath): array
    {
        $modulesConfig = implode(DIRECTORY_SEPARATOR, [
            $appRootPath,
            'config',
            'modules.php',
        ]);

        return file_exists($modulesConfig) ? include $modulesConfig : [];
    }
}

if (!function_exists('prependModulesToCfs')) {
    function prependModulesToCfs(array $appModules): void
    {
        $loadedModules = Kohana::modules();

        // Adding modules to CFS (they override /application/ and other core modules)
        foreach (array_reverse($appModules) as $moduleName => $modulePath) {
            if (isset($loadedModules[$moduleName])) {
                throw new \BetaKiller\Exception('Module :name already loaded from :path', [
                    ':name' => $moduleName,
                    ':path' => $modulePath,
                ]);
            }

            prependKohanaPath($modulePath);
        }
    }
}

if (!function_exists('prependKohanaPath')) {
    function prependKohanaPath(string $path): void
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        $reflection = new \ReflectionProperty(Kohana::class, '_paths');
        $reflection->setAccessible(true);

        $paths = $reflection->getValue();

        array_unshift($paths, $path);

        $reflection->setValue(null, $paths);
    }
}

if (!function_exists('initKohanaModules')) {
    function initKohanaModules(array $modules, ContainerInterface $container): void
    {
        $profiler = StartupProfiler::getInstance();

        $pm = $profiler->start(sprintf('Init modules (%d)', count($modules)));

        // Execute init.php in each module (if exists)
        foreach ($modules as $modulePath) {
            $initFile = rtrim($modulePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'init.php';

            if (file_exists($initFile)) {
                proceedModuleInit($initFile, $container);
            }
        }

        $profiler->stop($pm);
    }
}

if (!function_exists('proceedModuleInit')) {
    function proceedModuleInit(string $initFilePath, ContainerInterface $container): void
    {
        $moduleName = basename(dirname($initFilePath));

        $pmi = StartupProfiler::begin('Init module '.$moduleName);

        // Include the module initialization file once
        $className = include_once $initFilePath;

        if ($className && is_a($className, ModuleInitializerInterface::class, true)) {
            /** @var ModuleInitializerInterface $initializer */
            $initializer = $container->get($className);

            $initializer->initModule();
        }

        StartupProfiler::end($pmi);
    }
}

if (!function_exists('proceedAppInitFile')) {
    function proceedAppInitFile(string $appRootPath): void
    {
        // Loading custom init.php file for current site if exists
        $initFile = $appRootPath.DIRECTORY_SEPARATOR.'init.php';

        if (file_exists($initFile)) {
            require_once $initFile;
        }
    }
}

if (!function_exists('includeComposerAutoloader')) {
    function includeComposerAutoloader(string $rootPath): void
    {
        $vendorAutoload = implode(DIRECTORY_SEPARATOR, [
            $rootPath,
            'vendor',
            'autoload.php',
        ]);

        if (!file_exists($vendorAutoload)) {
            throw new LogicException('Init Composer in '.$rootPath);
        }

        require_once $vendorAutoload;
    }
}

if (!function_exists('patchKohana')) {
    function patchKohana(): void
    {
        // Drop cache because of init file
        // Inject fake config reader for resetting config groups cache (add last so no performance impact produced)
        Kohana::$config->attach(new FakeConfigReader(), false);

        // Reload site-related config (cache directory, profiling, errors, etc)
        Kohana::$config->load('init')->as_array();
    }
}

if (!function_exists('registerExceptionHandler')) {
    function registerExceptionHandler(): void
    {
        // Preload pretty stacktrace renderer (used in fallbackExceptionHandler)
        spl_autoload_call(Debug::class);

        set_exception_handler(function (Throwable $e) {
            fallbackExceptionHandler($e);
        });
    }
}

if (!function_exists('fallbackExceptionHandler')) {
    /**
     * @param \Throwable $e
     *
     * @return void
     */
    #[NoReturn]
    function fallbackExceptionHandler(Throwable $e): void
    {
        // Drop any output
        ob_get_length() && ob_end_clean();

        $appEnv = (class_exists(AppEnv::class, false) && AppEnv::isInitialized())
            ? AppEnv::instance()
            : null;

        if (!$appEnv || !class_exists(Debug::class, false)) {
            // Write to default log
            /** @noinspection ForgottenDebugOutputInspection */
            error_log('Startup error: '.$e->getMessage());
            exit(1);
        }

        $isCli   = $appEnv->isCli();
        $isDebug = $appEnv->isDebugEnabled();
        $inProd  = $appEnv->inProductionMode();

        if (!$isCli && !headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }

        // Detect root exception
        while ($e->getPrevious()) {
            $e = $e->getPrevious();
        }

        echo match (true) {
            // cli
            $isCli => PHP_EOL.$e->getMessage().PHP_EOL.PHP_EOL.$e->getTraceAsString().PHP_EOL.PHP_EOL,

            // non-cli in dev-friendly env
            !$inProd && $isDebug => Debug::htmlStackTrace($e),

            // non-cli without debug
            default => 'System error occurred.',
        };

        // Exit with error code
        exit(1);
    }
}
