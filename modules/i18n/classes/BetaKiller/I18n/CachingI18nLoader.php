<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

use Doctrine\Common\Cache\FilesystemCache;

class CachingI18nLoader implements I18nKeysLoaderInterface
{
    /**
     * @var \BetaKiller\I18n\I18nKeysLoaderInterface
     */
    private $proxy;

    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    private $cache;

    /**
     * CachingI18nLoader constructor.
     *
     * @param \BetaKiller\I18n\I18nKeysLoaderInterface $loader
     * @param string                                   $cachePath
     */
    public function __construct(I18nKeysLoaderInterface $loader, string $cachePath)
    {
        $this->proxy = $loader;
        $this->cache = new FilesystemCache($cachePath);
    }

    /**
     * @inheritDoc
     */
    public function loadI18nKeys(): array
    {
        $key = 'i18n.keys.cache';

        $data = $this->cache->fetch($key);

        if (!$data) {
            $data = $this->proxy->loadI18nKeys();
            $this->cache->save($key, $data, 30);
        }

        return $data;
    }

}
