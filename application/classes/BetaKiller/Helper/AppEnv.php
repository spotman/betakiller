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
     * @param bool|null $useStaging
     *
     * @return bool
     */
    public function inProduction($useStaging = null)
    {
        return Kohana::in_production((bool)$useStaging);
    }

    public function inDevelopmentMode()
    {
        return Kohana::$environment === Kohana::DEVELOPMENT;
    }

    public function getMode(): string
    {
        return Kohana::$environment_string;
    }
}
