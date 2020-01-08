<?php
namespace BetaKiller\Config;

final class KohanaConfigProvider implements ConfigProviderInterface
{
    public const KEY_SEPARATOR = '.';

    /**
     * @param string[] $path
     *
     * @return array|string|int|bool|null
     */
    public function load(array $path)
    {
        return \Kohana::$config->load(implode(self::KEY_SEPARATOR, $path));
    }
}
