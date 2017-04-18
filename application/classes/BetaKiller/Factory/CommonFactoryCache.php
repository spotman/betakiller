<?php
namespace BetaKiller\Factory;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\CacheProvider;

class CommonFactoryCache extends ChainCache implements FactoryCacheInterface
{
    /**
     * Constructor
     *
     * @param CacheProvider[] $cacheProviders
     */
    public function __construct($cacheProviders = [])
    {
        // Adding basic cache
        array_unshift($cacheProviders, new ArrayCache());

        parent::__construct($cacheProviders);
    }
}
