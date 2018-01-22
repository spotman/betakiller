<?php
namespace BetaKiller\Url;

/**
 * Interface UrlDispatcherCacheInterface
 *
 * @package BetaKiller\Url
 * @deprecated Use Doctrine\Common\Cache interface instead
 */
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

    public function delete(string $key): void;
}
