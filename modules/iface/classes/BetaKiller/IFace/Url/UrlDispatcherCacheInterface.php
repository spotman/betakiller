<?php
namespace BetaKiller\IFace\Url;

interface UrlDispatcherCacheInterface
{
    /**
     * @param $url
     *
     * @return array|null
     */
    public function get($url);

    /**
     * @param string $url
     * @param array  $item
     */
    public function set($url, array $item);
}
