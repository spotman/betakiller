<?php
namespace BetaKiller;

use Asm89\Twig\CacheExtension\CacheProvider\DoctrineCacheAdapter;
use Asm89\Twig\CacheExtension\CacheStrategy\BlackholeCacheStrategy;
use Asm89\Twig\CacheExtension\CacheStrategy\IndexedChainingCacheStrategy;
use Asm89\Twig\CacheExtension\CacheStrategy\LifetimeCacheStrategy;
use Asm89\Twig\CacheExtension\Extension as CacheExtension;
use BetaKiller\Env\AppEnvInterface;
use Doctrine\Common\Cache\ArrayCache;

class TwigCacheExtension extends CacheExtension
{
    /**
     * TwigCacheExtension constructor.
     *
     * @param \BetaKiller\Env\AppEnvInterface $appEnv
     */
    public function __construct(AppEnvInterface $appEnv)
    {
        if ($appEnv->inProductionMode() || $appEnv->inStagingMode()) {
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
