<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

use Psr\SimpleCache\CacheInterface;

class CachingI18nLoader implements I18nKeysLoaderInterface
{
    /**
     * @var \BetaKiller\I18n\I18nKeysLoaderInterface
     */
    private I18nKeysLoaderInterface $proxy;

    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    /**
     * CachingI18nLoader constructor.
     *
     * @param \BetaKiller\I18n\I18nKeysLoaderInterface $loader
     * @param \Psr\SimpleCache\CacheInterface          $cache
     */
    public function __construct(I18nKeysLoaderInterface $loader, CacheInterface $cache)
    {
        $this->proxy = $loader;
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function loadI18nKeys(): array
    {
        $key = 'i18n.keys.cache';

        $data = $this->cache->get($key);

        if (!$data) {
            $data = $this->proxy->loadI18nKeys();
            $this->cache->set($key, $data);
        }

        return $data;
    }
}
