<?php
namespace BetaKiller\Helper;

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
        $this->detectMode();
    }

    private function initDotEnv(): void
    {
        $dotEnv = new Dotenv($this->appRootPath, '.env');

        // Load local .env file if exists even in production
        if (file_exists($this->appRootPath.DIRECTORY_SEPARATOR.'.env')) {
            $dotEnv->load();
        }

        // App env (via SetEnv in .htaccess or VirtualHost)
        $dotEnv->required(self::APP_MODE)->notEmpty()->allowedValues(self::ALLOWED_MODES);

        // Current git revision (set upon deployment process)
        $dotEnv->required(self::APP_REVISION)->notEmpty();
    }

    private function detectMode(): void
    {
        $this->mode = getenv(self::APP_MODE);
    }

    /**
     * @param bool|null $useStaging
     *
     * @return bool
     */
    public function inProductionMode(?bool $useStaging = null): bool
    {
        $values = $useStaging
            ? [self::MODE_PRODUCTION, self::MODE_STAGING]
            : [self::MODE_PRODUCTION];

        return \in_array($this->mode, $values, true);
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
        return $this->debugEnabled || $this->inDevelopmentMode() || $this->inTestingMode();
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
        return getenv(self::APP_REVISION);
    }

    public function isCLI(): bool
    {
        return PHP_SAPI === 'cli';
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
     * @return null|string
     */
    public function getCliOption(string $name, ?bool $required = null): ?string
    {
        $key = $required ? $name : $name.'::';

        $options = \getopt('', [$key]);

        return $options[$name] ?? null;
    }
}
