<?php

namespace BetaKiller\Env;

use Dotenv\Dotenv;
use LogicException;

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
    private string $mode;

    /**
     * @var string
     */
    private string $url;

    /**
     * @var string
     */
    private string $revision;

    private string $coreRootPath;

    /**
     * @var string
     */
    private string $appRootPath;

    private string $docRootPath;

    private bool $isAppRunning;

    private bool $isCli;

    private bool $isCachingEnabled;

    public static function createFrom(array $envVars, array $serverVars): self
    {
        return self::$instance = new self($envVars, $serverVars);
    }

    public static function isInitialized(): bool
    {
        return (bool)self::$instance;
    }

    public static function instance(): AppEnvInterface
    {
        if (!self::$instance) {
            throw new LogicException('AppEnv must be initialized before calling AppEnv::instance()');
        }

        return self::$instance;
    }

    private function __construct(private readonly array $envVars, private readonly array $serverVars)
    {
        $this->isCli = $this->detectCli();

        $this->coreRootPath = realpath(DOCROOT);
        $this->docRootPath  = $this->detectDocRoot();
        $this->isAppRunning = !str_starts_with($this->docRootPath, $this->coreRootPath);
        $this->appRootPath  = $this->detectAppRoot();

        if ($this->isCli()) {
            $this->detectCliEnv(); // Cli options can override app configuration
        }

        $this->detectAppMode();
        $this->detectAppUrl();
        $this->detectAppRevision();
        $this->detectDebugMode();
        $this->detectCaching();
    }

    private function detectDebugMode(): void
    {
        if (!$this->inProductionMode() && $this->hasEnvVariable(self::APP_DEBUG)) {
            $this->enableDebug();
        }
    }

    private function detectAppMode(): void
    {
        $this->mode = $this->isAppRunning
            ? $this->getEnvVariable(self::APP_MODE)
            : AppEnvInterface::MODE_DEVELOPMENT;
    }

    private function detectAppUrl(): void
    {
        $this->url = $this->getEnvVariable(self::APP_URL);
    }

    private function detectAppRevision(): void
    {
        $required = $this->inProductionMode() || $this->inStagingMode();

        $this->revision = $required
            ? $this->getEnvVariable(self::APP_REVISION, true)
            : random_bytes(8);
    }

    private function detectCliEnv(): void
    {
        $debugOption = $this->getCliOption(AppEnvInterface::CLI_OPTION_DEBUG);

        if ($debugOption === 'true') {
            // Set variable
            putenv(self::APP_DEBUG.'=true');
        }

        if ($debugOption === 'false') {
            // Remove variable
            putenv(self::APP_DEBUG);
        }

        $stage = $this->getCliOption(AppEnvInterface::CLI_OPTION_STAGE);

        if ($stage) {
            \putenv(self::APP_MODE.'='.$stage);
        }
    }

    private function detectCaching(): void
    {
        $this->isCachingEnabled = $this->hasEnvVariable(self::APP_CACHE);
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
            throw new LogicException('Missing :name env variable', [':name' => $name]);
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
        return $this->docRootPath;
    }

    public function isAppRunning(): bool
    {
        return $this->isAppRunning;
    }

    public function isCli(): bool
    {
        return $this->isCli;
    }

    /**
     * @see https://stackoverflow.com/a/25967493
     * @return bool
     */
    private function detectCli(): bool
    {
        if ($this->isInternalWebServer()) {
            return false;
        }

        switch (true) {
            case PHP_SAPI === 'cli':
            case defined('STDIN'):
            case array_key_exists('SHELL', $this->envVars):
            case empty($this->serverVars['REMOTE_ADDR']) && empty($this->serverVars['HTTP_USER_AGENT']) && count(
                    $this->serverVars['argv']
                ) > 0:
            case !array_key_exists('REQUEST_METHOD', $this->serverVars):
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
        // Internal server is used for cache warmup only
        if ($this->isInternalWebServer()) {
            return false;
        }

        if ($this->isCli()) {
            return stream_isatty(STDOUT);
        }

        // Human otherwise
        return true;
    }

    /**
     * @param string    $name
     * @param bool|null $required
     *
     * @return null|string
     */
    public function getCliOption(string $name, bool $required = null): ?string
    {
        $key = $required ? $name : $name.'::';

        $options = \getopt('', [$key]);

        if ($options === false) {
            throw new LogicException('CLI arguments parsing error');
        }

        return $options[$name] ?? null;
    }

    public function hasCliOption(string $name): bool
    {
        return $this->getCliOption($name) !== null;
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
            $this->getAppRootPath(),
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
            $this->getAppRootPath(),
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
            $this->getAppRootPath(),
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

    public function isCachingEnabled(): bool
    {
        return $this->isCachingEnabled;
    }

    public function validateEnvVariables(): void
    {
        $dotEnv = Dotenv::create($this->getAppRootPath());

//        // Load local .env file if exists even in production, ignore missing file
//        $dotEnv->safeLoad();

        // App mode (via SetEnv in .htaccess or VirtualHost)
        $dotEnv->required(self::APP_MODE)->notEmpty()->allowedValues(self::ALLOWED_MODES);

        // Absolute URL with scheme
        $dotEnv->required(self::APP_URL)->notEmpty();
    }

    private function checkFileDirectoryExists(string $filePath): void
    {
        // Get base directory
        $dir = \dirname($filePath);

        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
    }

    private function getServerScriptFilename(): string
    {
        return realpath($this->serverVars['SCRIPT_FILENAME']);
    }

    private function getServerDocumentRoot(): ?string
    {
        return $this->serverVars['DOCUMENT_ROOT'] ?? null;
    }

    private function detectDocRoot(): string
    {
        // Web requests must use http server variables
        if (!$this->isCli) {
            return $this->getServerDocumentRoot();
        }

        // CLI calls must use initial script` directory
        return dirname($this->getServerScriptFilename());
    }

    private function detectAppRoot(): string
    {
        if (!$this->isAppRunning) {
            return $this->coreRootPath;
        }

        if ($this->isCli && str_contains($this->docRootPath, implode(DIRECTORY_SEPARATOR, ['vendor', 'bin']))) {
            return getcwd();
        }

        if (!str_ends_with($this->docRootPath, DIRECTORY_SEPARATOR.'public')) {
            throw new LogicException('Application document root must be set to "public" subdirectory');
        }

        // Parent of public dir
        return realpath($this->docRootPath.DIRECTORY_SEPARATOR.'..');
    }
}
