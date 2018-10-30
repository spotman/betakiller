<?php
namespace BetaKiller\Helper;

use BetaKiller\Exception;
use Dotenv\Dotenv;

/**
 * Class AppEnv
 *
 * @package BetaKiller\Helper
 */
class AppEnv implements AppEnvInterface
{
    /**
     * @var bool
     */
    private $debugEnabled = false;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var string
     */
    private $appRootPath;

    /**
     * @var string
     */
    private $docRootPath;

    /**
     * @var bool
     */
    private $isCoreRunning;

    /**
     * AppEnv constructor.
     *
     * @param string $appRoot
     * @param string $docRoot
     * @param bool   $isCoreRunning
     */
    public function __construct(string $appRoot, string $docRoot, bool $isCoreRunning)
    {
        $this->appRootPath   = $appRoot;
        $this->docRootPath   = $docRoot;
        $this->isCoreRunning = $isCoreRunning;

        $this->initDotEnv();
        $this->detectCliEnv();
        $this->detectAppMode();
    }

    private function initDotEnv(): void
    {
        $dotEnv = new Dotenv($this->appRootPath, '.env');

        // Load local .env file if exists even in production, ignore missing file
        $dotEnv->safeLoad();

        // App absolute URL with scheme
        $dotEnv->required(self::APP_URL)->notEmpty();

        // App mode (via SetEnv in .htaccess or VirtualHost)
        $dotEnv->required(self::APP_MODE)->notEmpty()->allowedValues(self::ALLOWED_MODES);

        // Current git revision (set upon deployment process)
        $dotEnv->required(self::APP_REVISION)->notEmpty();
    }

    private function detectAppMode(): void
    {
        $this->mode = $this->getEnvVariable(self::APP_MODE);

        if ($this->inDevelopmentMode()) {
            $this->enableDebug();
        }
    }

    private function detectCliEnv(): void
    {
        if (!$this->isCli()) {
            return;
        }

        if ($this->getCliOption('debug', false, 'false') !== 'false') {
            $this->enableDebug();
        }

        $stage = $this->getCliOption('stage');

        if ($stage) {
            \putenv(self::APP_MODE.'='.$stage);
        }
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
            throw new Exception('Missing :name env variable', [':name' => $name]);
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

    public function isDebugEnabled(): bool
    {
        return $this->debugEnabled;
    }

    public function enableDebug(): void
    {
        $this->debugEnabled = true;
    }

    public function getModeName(): string
    {
        return $this->mode;
    }

    public function getRevisionKey(): string
    {
        return $this->getEnvVariable(self::APP_REVISION);
    }

    /**
     * @see https://stackoverflow.com/a/25967493
     * @return bool
     */
    public function isCli(): bool
    {
        if (PHP_SAPI === 'cli') {
            return true;
        }

        if ($this->isInternalWebServer()) {
            return false;
        }

        if (\defined('STDIN')) {
            return true;
        }

        if (array_key_exists('SHELL', $_ENV)) {
            return true;
        }

        if (empty($_SERVER['REMOTE_ADDR']) && empty($_SERVER['HTTP_USER_AGENT']) && \count($_SERVER['argv']) > 0) {
            return true;
        }

        if (!array_key_exists('REQUEST_METHOD', $_SERVER)) {
            return true;
        }

        return false;
    }

    public function getAppRootPath(): string
    {
        return $this->appRootPath;
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
     * @return string
     */
    public function getDocRootPath(): string
    {
        return $this->docRootPath;
    }

    /**
     * @return string
     */
    public function getAppCodename(): string
    {
        return basename($this->appRootPath);
    }

    /**
     * Returns true if this is a core run (core console commands, CI tests, etc)
     *
     * @return bool
     */
    public function isCoreRunning(): bool
    {
        return $this->isCoreRunning;
    }

    /**
     * @param string    $name
     *
     * @param bool|null $required
     *
     * @param string    $default
     *
     * @return null|string
     */
    public function getCliOption(string $name, bool $required = null, string $default = null): ?string
    {
        $key = $required ? $name : $name.'::';

        $options = \getopt('', [$key]);

        return $options[$name] ?? $default;
    }

    /**
     * Returns absolute path to the global temp directory (must be readable/writable between requests)
     *
     * @see https://serverfault.com/a/615054
     * @return string
     */
    public function getTempPath(): string
    {
        return \sys_get_temp_dir();
    }

    /**
     * Returns email which will receive all emails in debug mode
     *
     * @return string
     */
    public function getDebugEmail(): string
    {
        return $this->getEnvVariable('DEBUG_EMAIL_ADDRESS');
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
}
