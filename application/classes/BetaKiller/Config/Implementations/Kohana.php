<?php
namespace BetaKiller\Config\Implementations;

use BetaKiller\Config\ConfigInterface;

abstract class Kohana implements ConfigInterface
{
    /**
     * @param string $group
     * @return \BetaKiller\Config\ConfigGroupInterface|array|string|null
     */
    public function load($group)
    {
        return \Kohana::config($group);
    }
}
