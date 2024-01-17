<?php
namespace BetaKiller\Config;

use Kohana;

final class KohanaConfigProvider implements ConfigProviderInterface
{
    public const KEY_SEPARATOR = '.';

    /**
     * @param string   $group
     * @param string[] $path
     *
     * @return array|string|int|bool|null
     * @throws \Kohana_Exception
     */
    public function load(string $group, array $path): array|string|int|bool|null
    {
        if (!$path) {
            return Kohana::$config->load($group)->as_array();
        }

        array_unshift($path, $group);

        return Kohana::$config->load(implode(self::KEY_SEPARATOR, $path));
    }
}
