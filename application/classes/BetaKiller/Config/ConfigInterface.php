<?php
namespace BetaKiller\Config;

interface ConfigInterface
{
    /**
     * @param array $group
     * @return \BetaKiller\Config\ConfigGroupInterface|array|string|null
     */
    public function load(array $group);
}
