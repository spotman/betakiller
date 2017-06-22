<?php
namespace BetaKiller\Config\Implementations;

use BetaKiller\Config\ConfigProviderInterface;

abstract class Kohana implements ConfigProviderInterface
{
    const KEY_SEPARATOR = '.';

    /**
     * @param array $group
     * @return \BetaKiller\Config\ConfigGroupInterface|array|string|null
     */
    public function load(array $group)
    {
        return \Kohana::config(implode(self::KEY_SEPARATOR, $group));
    }
}
