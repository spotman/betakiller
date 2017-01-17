<?php
namespace BetaKiller\Helper;

trait InProduction
{
    private function in_production($use_staging = FALSE)
    {
        return \Kohana::in_production($use_staging);
    }
}
