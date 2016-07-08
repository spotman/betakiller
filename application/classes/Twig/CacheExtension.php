<?php

use Doctrine\Common\Cache\ArrayCache;
use Asm89\Twig\CacheExtension\CacheProvider\DoctrineCacheAdapter;
use Asm89\Twig\CacheExtension\CacheStrategy\LifetimeCacheStrategy;
use Asm89\Twig\CacheExtension\CacheStrategy\BlackholeCacheStrategy;
use Asm89\Twig\CacheExtension\CacheStrategy\IndexedChainingCacheStrategy;
use Asm89\Twig\CacheExtension\Extension as CacheExtension;

class Twig_CacheExtension extends CacheExtension
{
    public function __construct()
    {
        if (Kohana::in_production(TRUE))
        {
            $cacheProvider  = new DoctrineCacheAdapter(new ArrayCache());
            $lifetimeCacheStrategy  = new LifetimeCacheStrategy($cacheProvider);

            $cacheStrategy  = new IndexedChainingCacheStrategy(array(
                'time' => $lifetimeCacheStrategy,
//            'gen'  => $generationalCacheStrategy,
            ));
        }
        else
        {
            $cacheStrategy = new BlackholeCacheStrategy();
        }

        parent::__construct($cacheStrategy);
    }
}
