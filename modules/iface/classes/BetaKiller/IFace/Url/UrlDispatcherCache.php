<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Model\DispatchableEntityInterface;
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
    public function __construct(array $cacheProviders = null)
    {
        $cacheProviders = $cacheProviders ?? [];

        // Prepend basic cache
        array_unshift($cacheProviders, new ArrayCache());

        $this->cache = new ChainCache($cacheProviders);
    }


    /**
     * @param string $url
     *
     * @return array|null
     */
    public function get(string $url)
    {
        $data = $this->cache->fetch($url);

        return unserialize($data, [
            IFaceInterface::class,
            DispatchableEntityInterface::class,
        ]);
    }

    /**
     * @param string $url
     * @param array  $item
     */
    public function set(string $url, array $item): void
    {
        $this->cache->save($url, serialize($item));
    }
}
