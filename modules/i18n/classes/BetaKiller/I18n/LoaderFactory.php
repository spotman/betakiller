<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

use Psr\SimpleCache\CacheInterface;

class LoaderFactory
{
    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    private $cache;

    /**
     * @var \BetaKiller\I18n\LazyAggregateLoader
     */
    private $lazyLoader;


    /**
     * LoaderFactory constructor.
     *
     * @param \BetaKiller\I18n\LazyAggregateLoader $lazyLoader
     * @param \Psr\SimpleCache\CacheInterface      $cache
     */
    public function __construct(
        LazyAggregateLoader $lazyLoader,
        CacheInterface $cache
    ) {
        $this->cache      = $cache;
        $this->lazyLoader = $lazyLoader;
    }

    public function create(): LoaderInterface
    {
        return new CachingLoader($this->lazyLoader, $this->cache);
    }
}
