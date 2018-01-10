<?php
namespace BetaKiller\Url;

interface UrlDispatcherCacheInterface
{
    /**
     * @param $url
     *
     * @return array|null
     */
    public function get(string $url);

    /**
     * @param string $url
     * @param array  $item
     */
    public function set(string $url, array $item);
}
