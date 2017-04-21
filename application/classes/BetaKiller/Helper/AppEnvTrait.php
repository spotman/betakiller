<?php
namespace BetaKiller\Helper;

use Kohana;

trait AppEnvTrait
{
    private function in_production($use_staging = FALSE)
    {
        return Kohana::in_production($use_staging);
    }

    private function inDevelopmentMode()
    {
        return Kohana::$environment === Kohana::DEVELOPMENT;
    }
}
