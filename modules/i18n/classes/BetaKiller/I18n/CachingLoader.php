<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

use Psr\SimpleCache\CacheInterface;

class CachingLoader implements LoaderInterface
{
    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    private $cache;

    /**
     * @var \BetaKiller\I18n\LoaderInterface
     */
    private $proxy;

    /**
     * CachingLoader constructor.
     *
     * @param \BetaKiller\I18n\LoaderInterface $proxy
     * @param \Psr\SimpleCache\CacheInterface  $cache
     */
    public function __construct(LoaderInterface $proxy, CacheInterface $cache)
    {
        $this->cache = $cache;
        $this->proxy = $proxy;
    }

    /**
     * Returns "key" => "translated string" pairs for provided locale
     *
     * @param string $locale
     *
     * @return string[]
     */
    public function load(string $locale): array
    {
        $key = 'i18n-'.$locale;

        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $data = $this->proxy->load($locale);

        $this->cache->set($key, $data);

        return $data;
    }
}
