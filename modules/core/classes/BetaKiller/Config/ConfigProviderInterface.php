<?php
namespace BetaKiller\Config;

interface ConfigProviderInterface
{
    /**
     * @param string $group
     * @param array  $path
     *
     * @return array|string|int|bool|null
     */
    public function load(string $group, array $path): array|string|int|bool|null;
}
