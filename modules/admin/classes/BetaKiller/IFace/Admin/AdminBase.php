<?php
namespace BetaKiller\IFace\Admin;

use BetaKiller\IFace\IFace;

abstract class AdminBase extends IFace
{
    public function getDefaultExpiresInterval()
    {
        // No caching for admin zone
        $interval         = new \DateInterval('PT1H');
        $interval->invert = 1;

        return $interval;
    }
}
