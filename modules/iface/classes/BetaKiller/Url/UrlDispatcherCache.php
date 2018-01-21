<?php
namespace BetaKiller\Url;

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
     * @param string $key
     *
     * @return array|null
     */
    public function get(string $key): ?array
    {
        $data = $this->cache->fetch($key);

        return unserialize($data, [
            IFaceInterface::class,
            DispatchableEntityInterface::class,
        ]);
    }

    /**
     * @param string $key
     * @param array  $item
     */
    public function set(string $key, array $item): void
    {
        $this->cache->save($key, serialize($item));
    }

    public function clear(string $key): void
    {
        $this->cache->delete($key);
    }
}
