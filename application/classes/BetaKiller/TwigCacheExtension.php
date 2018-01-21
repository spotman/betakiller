<?php
namespace BetaKiller;

use Asm89\Twig\CacheExtension\CacheProvider\DoctrineCacheAdapter;
use Asm89\Twig\CacheExtension\CacheStrategy\BlackholeCacheStrategy;
use Asm89\Twig\CacheExtension\CacheStrategy\IndexedChainingCacheStrategy;
use Asm89\Twig\CacheExtension\CacheStrategy\LifetimeCacheStrategy;
use Asm89\Twig\CacheExtension\Extension as CacheExtension;
use Doctrine\Common\Cache\ArrayCache;
use Kohana;

class TwigCacheExtension extends CacheExtension
{
    public function __construct()
    {
        if (Kohana::inProduction(true)) {
            $cacheProvider         = new DoctrineCacheAdapter(new ArrayCache());
            $lifetimeCacheStrategy = new LifetimeCacheStrategy($cacheProvider);

            $cacheStrategy = new IndexedChainingCacheStrategy([
                'time' => $lifetimeCacheStrategy,
//            'gen'  => $generationalCacheStrategy,
            ]);
        } else {
            $cacheStrategy = new BlackholeCacheStrategy();
        }

        parent::__construct($cacheStrategy);
    }
}
