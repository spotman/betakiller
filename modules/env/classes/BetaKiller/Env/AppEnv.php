<?php
namespace BetaKiller\Env;

use Dotenv\Dotenv;
use function stream_isatty;

/**
 * Class AppEnv
 *
 * @package BetaKiller\Helper
 */
final class AppEnv implements AppEnvInterface
{
    private static ?AppEnvInterface $instance = null;

    /**
     * @var bool
     */
    private bool $debugEnabled = false;

    /**
     * @var string
     */
    private string $mode = AppEnvInterface::MODE_PRODUCTION;

    /**
     * @var string
     */
    private string $url;

    /**
     * @var string
     */
    private string $revision;

    /**
     * @var string
     */
    private string $appRootPath;

    private bool $isAppRunning = false;

    public static function instance(): AppEnvInterface
    {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct()
    {
        if ($this->isCli()) {
            $this->detectCliEnv(); // Cli options can override app configuration
        }

        $this->detectAppRunning();

        if ($this->isAppRunning) {
            $this->detectAppMode();
            $this->detectAppRoot();
            $this->detectAppUrl();
            $this->detectAppRevision();
            $this->detectDebugMode();
        }
    }

    private function detectDebugMode(): void
    {
        if (!$this->inProductionMode() && $this->hasEnvVariable(self::APP_DEBUG)) {
            $this->enableDebug();
        }
    }

    private function detectAppMode(): void
    {
        $this->mode = $this->getEnvVariable(self::APP_MODE);
    }

    private function detectAppRoot(): void
    {
        $this->appRootPath = $this->getEnvVariable(self::APP_ROOT_PATH);
    }

    private function detectAppUrl(): void
    {
        $this->url = $this->getEnvVariable(self::APP_URL);
    }

    private function detectAppRevision(): void
    {
        $this->revision = $this->getEnvVariable(self::APP_REVISION);
    }

    private function detectAppRunning(): void
    {
        $this->isAppRunning = $this->hasEnvVariable(self::APP_ROOT_PATH);
    }

    private function detectCliEnv(): void
    {
        $debugOption = $this->getCliOption('debug', false);

        if ($debugOption === 'true') {
            // Set variable
            putenv(self::APP_DEBUG.'=true');
        }

        if ($debugOption === 'false') {
            // Remove variable
            putenv(self::APP_DEBUG);
        }

        $stage = $this->getCliOption('stage');

        if ($stage) {
            \putenv(self::APP_MODE.'='.$stage);
        }
    }

    /**
     * @inheritDoc
     */
    public function hasEnvVariable(string $name): bool
    {
        $name = \mb_strtoupper($name);

        return !empty(getenv($name));
    }

    /**
     * @param string $name
     * @param bool   $required
     *
     * @return string
     */
    public function getEnvVariable(string $name, bool $required = null): string
    {
        $name  = \mb_strtoupper($name);
        $value = getenv($name);

        if (!$value && $required) {
            throw new \LogicException('Missing :name env variable', [':name' => $name]);
        }

        return (string)$value;
    }

    /**
     * @return bool
     */
    public function inProductionMode(): bool
    {
        return $this->mode === self::MODE_PRODUCTION;
    }

    public function inDevelopmentMode(): bool
    {
        return $this->mode === self::MODE_DEVELOPMENT;
    }

    public function inTestingMode(): bool
    {
        return $this->mode === self::MODE_TESTING;
    }

    public function inStagingMode(): bool
    {
        return $this->mode === self::MODE_STAGING;
    }

    public function getModeName(): string
    {
        return $this->mode;
    }

    public function getRevisionKey(): string
    {
        return $this->revision;
    }

    /**
     * @return string
     */
    public function getAppCodename(): string
    {
        return basename($this->appRootPath);
    }

    public function getAppUrl(): string
    {
        return $this->url;
    }

    public function getAppRootPath(): string
    {
        return $this->appRootPath;
    }

    /**
     * @return string
     */
    public function getDocRootPath(): string
    {
        return $this->appRootPath.DIRECTORY_SEPARATOR.'public';
    }

    public function isAppRunning(): bool
    {
        return $this->isAppRunning;
    }

    /**
     * @see https://stackoverflow.com/a/25967493
     * @return bool
     */
    public function isCli(): bool
    {
        if ($this->isInternalWebServer()) {
            return false;
        }

        switch (true) {
            case PHP_SAPI === 'cli':
            case \defined('STDIN'):
            case array_key_exists('SHELL', $_ENV):
            case empty($_SERVER['REMOTE_ADDR']) && empty($_SERVER['HTTP_USER_AGENT']) && \count($_SERVER['argv']) > 0:
            case !array_key_exists('REQUEST_METHOD', $_SERVER):
                return true;

            default:
                return false;
        }
    }

    /**
     * Returns true when app was server by an internal PHP web-server (php -S)
     *
     * @return bool
     */
    public function isInternalWebServer(): bool
    {
        return PHP_SAPI === 'cli-server';
    }

    /**
     * Returns true if current script is executed by a human
     *
     * @return bool
     */
    public function isHuman(): bool
    {
        if ($this->isCli()) {
            return stream_isatty(STDOUT);
        }

        // Human otherwise
        return true;
    }

    /**
     * @param string      $name
     *
     * @param bool|null   $required
     * @param string|null $default
     *
     * @return null|string
     */
    public function getCliOption(string $name, bool $required = null, string $default = null): ?string
    {
        $key = $required ? $name : $name.'::';

        $options = \getopt('', [$key]);

        if ($options === false) {
            throw new \LogicException('CLI arguments parsing error');
        }

        return $options[$name] ?? $default;
    }

    /**
     * Returns absolute path to the global temp directory (must be readable/writable between requests)
     *
     * @see https://serverfault.com/a/615054
     *
     * @param string $target
     *
     * @return string
     */
    public function getTempPath(string $target): string
    {
        $envKey = $this->isAppRunning
            ? $this->getAppCodename().'.'.$this->getModeName()
            : 'core';

        $path = implode(\DIRECTORY_SEPARATOR, [
            \sys_get_temp_dir(),
            $envKey,
            $target,
        ]);

        $this->checkFileDirectoryExists($path);

        return $path;
    }

    /**
     * Returns path to directory used as a permanent storage
     *
     * @param string $target Relative path to add
     *
     * @return string
     */
    public function getStoragePath(string $target): string
    {
        $path = implode(\DIRECTORY_SEPARATOR, [
            $this->getWorkingPath(),
            'storage',
            $target,
        ]);

        $this->checkFileDirectoryExists($path);

        return $path;
    }

    /**
     * @inheritDoc
     */
    public function getLogsPath(string $target): string
    {
        $path = implode(\DIRECTORY_SEPARATOR, [
            $this->getWorkingPath(),
            'logs',
            $target,
        ]);

        $this->checkFileDirectoryExists($path);

        return $path;
    }

    /**
     * @inheritDoc
     */
    public function getCachePath(string $target): string
    {
        $path = implode(\DIRECTORY_SEPARATOR, [
            $this->getWorkingPath(),
            'cache',
            $target,
        ]);

        $this->checkFileDirectoryExists($path);

        return $path;
    }

    /**
     * Returns email which will receive all emails in debug mode
     *
     * @return string
     */
    public function getDebugEmail(): string
    {
        return $this->getEnvVariable(self::DEBUG_EMAIL_ADDRESS);
    }

    public function isDebugEnabled(): bool
    {
        return $this->debugEnabled;
    }

    public function enableDebug(): void
    {
        $this->debugEnabled = true;
    }

    /**
     * Call this method to disable debug mode
     */
    public function disableDebug(): void
    {
        $this->debugEnabled = false;
    }

    public function validateEnvVariables(): void
    {
        $dotEnv = Dotenv::create($this->getAppRootPath());

//        // Load local .env file if exists even in production, ignore missing file
//        $dotEnv->safeLoad();

        // App mode (via SetEnv in .htaccess or VirtualHost)
        $dotEnv->required(self::APP_MODE)->notEmpty()->allowedValues(self::ALLOWED_MODES);

        // Absolute path to app root
        $dotEnv->required(self::APP_ROOT_PATH)->notEmpty();

        // Absolute URL with scheme
        $dotEnv->required(self::APP_URL)->notEmpty();

        // Current git revision (set upon deployment process)
        $dotEnv->required(self::APP_REVISION)->notEmpty();
    }

    private function checkFileDirectoryExists(string $filePath): void
    {
        // Get base directory
        $dir = \dirname($filePath);

        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
    }

    private function getWorkingPath(): string
    {
        return $this->isAppRunning ? $this->appRootPath : DOCROOT;
    }
}
