<?php
namespace BetaKiller\Helper;

/**
 * Class AppEnv
 *
 * @package BetaKiller\Helper
 */
interface AppEnvInterface
{
    // Common environment type constants for consistency and convenience
    public const MODE_PRODUCTION  = 'production';
    public const MODE_DEVELOPMENT = 'development';
    public const MODE_STAGING     = 'staging';
    public const MODE_TESTING     = 'testing';

    public const ALLOWED_MODES = [
        AppEnvInterface::MODE_PRODUCTION,
        AppEnvInterface::MODE_STAGING,
        AppEnvInterface::MODE_TESTING,
        AppEnvInterface::MODE_DEVELOPMENT,
    ];

    public const APP_MODE     = 'APP_ENV';
    public const APP_REVISION = 'APP_REVISION';

    /**
     * @param bool|null $useStaging
     *
     * @return bool
     */
    public function inProductionMode(?bool $useStaging = null): bool;

    /**
     * @return bool
     */
    public function inDevelopmentMode(): bool;

    /**
     * @return bool
     */
    public function inTestingMode(): bool;

    /**
     * @return bool
     */
    public function inStagingMode(): bool;

    /**
     * @return bool
     */
    public function isDebugEnabled(): bool;

    /**
     * Call this method to enable debug mode
     */
    public function enableDebug(): void;

    /**
     * @return string
     */
    public function getModeName(): string;

    /**
     * @return string
     */
    public function getRevisionKey(): string;

    /**
     * @return bool
     */
    public function isCLI(): bool;

    /**
     * Returns application root path
     *
     * @return string
     */
    public function getAppRootPath(): string;

    /**
     * Returns path marked as document root in web-server config
     *
     * @return string
     */
    public function getDocRootPath(): string;

    /**
     * @return string
     */
    public function getAppCodename(): string;

    /**
     * Returns true if this is a core run (core console commands, CI tests, etc)
     *
     * @return bool
     */
    public function isCoreRunning(): bool;
}
