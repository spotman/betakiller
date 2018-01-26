<?php
namespace BetaKiller;

use Asm89\Twig\CacheExtension\CacheProvider\DoctrineCacheAdapter;
use Asm89\Twig\CacheExtension\CacheStrategy\BlackholeCacheStrategy;
use Asm89\Twig\CacheExtension\CacheStrategy\IndexedChainingCacheStrategy;
use Asm89\Twig\CacheExtension\CacheStrategy\LifetimeCacheStrategy;
use Asm89\Twig\CacheExtension\Extension as CacheExtension;
use BetaKiller\DI\Container;
use Doctrine\Common\Cache\ArrayCache;

class TwigCacheExtension extends CacheExtension
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\AppEnv
     */
    private $appEnv;

    /**
     * TwigCacheExtension constructor.
     *
     * @throws \InvalidArgumentException
     * @throws \DI\DependencyException
     */
    public function __construct()
    {
        Container::getInstance()->injectOn($this);

        if ($this->appEnv->inProductionMode(true)) {
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
