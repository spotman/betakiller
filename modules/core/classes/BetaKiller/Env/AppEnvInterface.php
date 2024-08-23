<?php
namespace BetaKiller\Env;

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

    public const APP_URL      = 'APP_URL';
    public const APP_MODE     = 'APP_ENV';
    public const APP_REVISION = 'APP_REVISION';
    public const APP_DEBUG    = 'APP_DEBUG';
    public const APP_CACHE    = 'APP_CACHE';

    public const DEBUG_EMAIL_ADDRESS = 'DEBUG_EMAIL_ADDRESS';

    public const CLI_OPTION_STAGE     = 'stage';
    public const CLI_OPTION_LOG_LEVEL = 'loglevel';
    public const CLI_OPTION_DEBUG     = 'debug';
    public const CLI_OPTION_USER      = 'user';

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasEnvVariable(string $name): bool;

    /**
     * @param string $name
     * @param bool   $required
     *
     * @return string
     */
    public function getEnvVariable(string $name, bool $required = null): string;

    /**
     * @return bool
     */
    public function inProductionMode(): bool;

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
     * @return string
     */
    public function getModeName(): string;

    /**
     * @return string
     */
    public function getRevisionKey(): string;

    /**
     * Returns application URL
     *
     * @return string
     */
    public function getAppUrl(): string;

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
     * @param string      $name
     * @param bool|null   $required
     *
     * @param string|null $default
     *
     * @return null|string
     */
    public function getCliOption(string $name, bool $required = null, string $default = null): ?string;

    /**
     * Returns false if this is a core run (core console commands, CI tests, etc)
     *
     * @return bool
     */
    public function isAppRunning(): bool;

    /**
     * @return bool
     */
    public function isCli(): bool;

    /**
     * Returns true when app was server by an internal PHP web-server (php -S)
     *
     * @return bool
     */
    public function isInternalWebServer(): bool;

    /**
     * Returns true if current script is executed by a human
     *
     * @return bool
     */
    public function isHuman(): bool;

    /**
     * Returns full path for logging purpose
     * Appends provided target to the app root path
     *
     * @param string $target
     *
     * @return string
     */
    public function getLogsPath(string $target): string;

    /**
     * Returns full path for filesystem cache purpose
     * Appends provided target to the app root path
     *
     * @param string $target
     *
     * @return string
     */
    public function getCachePath(string $target): string;

    /**
     * Returns absolute path to a file inside the project`s temp directory
     * (must be readable/writable between requests)
     *
     * @see https://serverfault.com/a/615054
     *
     * @param string $target
     *
     * @return string
     */
    public function getTempPath(string $target): string;

    /**
     * Returns path to directory used as a permanent storage
     *
     * @param string $target
     *
     * @return string
     */
    public function getStoragePath(string $target): string;

    /**
     * @return bool
     */
    public function isDebugEnabled(): bool;

    /**
     * Call this method to enable debug mode
     */
    public function enableDebug(): void;

    /**
     * Call this method to disable debug mode
     */
    public function disableDebug(): void;

    /**
     * Returns email which will receive all emails in debug mode
     *
     * @return string
     */
    public function getDebugEmail(): string;

    /**
     * @return void
     */
    public function validateEnvVariables(): void;

    /**
     * @return bool
     */
    public function isCachingEnabled(): bool;
}
