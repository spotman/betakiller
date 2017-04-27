<?php
namespace BetaKiller\IFace\Url;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;

class UrlDispatcherCache implements UrlDispatcherCacheInterface
{
    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    private $cache;

    /**
     * UrlDispatcherCache constructor.
     *
     * @param \Doctrine\Common\Cache\Cache[] $cacheProviders
     */
    public function __construct(array $cacheProviders = [])
    {
        // Prepend basic cache
        array_unshift($cacheProviders, new ArrayCache());

        $this->cache = new ChainCache($cacheProviders);
    }


    /**
     * @param $url
     *
     * @return array|null
     */
    public function get($url)
    {
        $data = $this->cache->fetch($url);

        return unserialize($data);
    }

    /**
     * @param string $url
     * @param array  $item
     */
    public function set($url, array $item)
    {
        $this->cache->save($url, serialize($item));
    }

}
