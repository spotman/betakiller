<?php
namespace BetaKiller\Helper;

use Kohana;

/**
 * Class AppEnv
 *
 * @package BetaKiller\Helper
 */
class AppEnv
{
    /**
     * @var bool
     */
    private $debugEnabled = false;

    /**
     * @param bool|null $useStaging
     *
     * @return bool
     */
    public function inProduction($useStaging = null): bool
    {
        return Kohana::in_production((bool)$useStaging);
    }

    public function inDevelopmentMode(): bool
    {
        return Kohana::$environment === Kohana::DEVELOPMENT;
    }

    public function inTestingMode(): bool
    {
        return Kohana::$environment === Kohana::TESTING;
    }

    public function inStagingMode(): bool
    {
        return Kohana::$environment === Kohana::STAGING;
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
        return Kohana::$environment_string;
    }

    public function isCLI(): bool
    {
        return PHP_SAPI === 'cli';
    }
}
