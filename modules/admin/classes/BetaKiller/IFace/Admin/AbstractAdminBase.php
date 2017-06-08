<?php
namespace BetaKiller\IFace\Admin;

use BetaKiller\IFace\AbstractIFace;

abstract class AbstractAdminBase extends AbstractIFace
{
    public function getDefaultExpiresInterval(): \DateInterval
    {
        // No caching for admin zone
        $interval         = new \DateInterval('PT1H');
        $interval->invert = 1;

        return $interval;
    }
}
