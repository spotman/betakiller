<?php
namespace BetaKiller\Url;

interface UrlDispatcherCacheInterface
{
    /**
     * @param string $key
     *
     * @return array|null
     */
    public function get(string $key): ?array;

    /**
     * @param string $key
     * @param array  $item
     */
    public function set(string $key, array $item);

    public function clear(string $key): void;
}
