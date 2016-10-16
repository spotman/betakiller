<?php
namespace BetaKiller\Config;

interface ConfigInterface
{
    /**
     * @param string $group
     * @return \BetaKiller\Config\ConfigGroupInterface|array|string|null
     */
    public function load($group);
}
