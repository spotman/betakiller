<?php
namespace BetaKiller\Config;


interface ConfigGroupInterface
{
    /**
     * @return array
     */
    public function asArray(): array;

    /**
     * @param string $key
     * @param mixed|null $default
     * @return array|string
     */
    public function get($key, $default = NULL);
}
