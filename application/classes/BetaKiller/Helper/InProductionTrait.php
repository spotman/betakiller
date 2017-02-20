<?php
namespace BetaKiller\Helper;

trait InProductionTrait
{
    private function in_production($use_staging = FALSE)
    {
        return \Kohana::in_production($use_staging);
    }
}
