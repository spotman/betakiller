<?php
namespace BetaKiller\Config;

interface ConfigProviderInterface
{
    /**
     * @param array $group
     * @return array|string|int|bool|null
     */
    public function load(array $group);
}
