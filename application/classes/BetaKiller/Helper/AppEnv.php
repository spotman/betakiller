<?php
namespace BetaKiller\Helper;

use BetaKiller\Exception;
use Dotenv\Dotenv;

/**
 * Class AppEnv
 *
 * @package BetaKiller\Helper
 */
class AppEnv
{
    // Common environment type constants for consistency and convenience
    public const MODE_PRODUCTION  = 'production';
    public const MODE_STAGING     = 'staging';
    public const MODE_TESTING     = 'testing';
    public const MODE_DEVELOPMENT = 'development';

    public const ALLOWED_MODES = [
        self::MODE_PRODUCTION,
        self::MODE_STAGING,
        self::MODE_TESTING,
        self::MODE_DEVELOPMENT,
    ];

    public const APP_MODE     = 'APP_ENV';
    public const APP_REVISION = 'APP_REVISION';

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
    private $appRoot;

    /**
     * AppEnv constructor.
     *
     * @param string $appRoot
     *
     */
    public function __construct(string $appRoot)
    {
        $this->appRoot = $appRoot;

        $this->initDotEnv();
        $this->detectMode();
    }

    private function initDotEnv(): void
    {
        $dotEnv = new Dotenv($this->appRoot, '.env');

        // Load local .env file if exists even in production
        if (file_exists($this->appRoot.DIRECTORY_SEPARATOR.'.env')) {
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
}
