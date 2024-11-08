<?php

declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Helper\LoggerHelper;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Throwable;

use function count;

final readonly class UrlElementTreeFactory
{
    private const CACHE_KEY = 'ifaceModelTree';

    public function __construct(
        private UrlElementTreeLoader $loader,
        private UrlElementTreeValidatorInterface $validator,
        private CacheInterface $cache,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(): UrlElementTreeInterface
    {
        $tree = new UrlElementTree();

        if (!$this->loadFromCache($tree)) {
            // Fetch from loader
            $this->loader->loadInto($tree);

            // Check for errors
            $this->validator->validate($tree);

            $this->storeInCache($tree);
        }

        return $tree;
    }

    /**
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     *
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function loadFromCache(UrlElementTreeInterface $tree): bool
    {
        $serializedModels = $this->cache->get(self::CACHE_KEY);

        if (!$serializedModels) {
            return false;
        }

        $this->logger->debug('Loading URL elements tree from cache');
        $counter = 0;

        try {
            $data = unserialize($serializedModels, [UrlElementInterface::class]);

            if (!$data || !\is_array($data)) {
                throw new UrlElementException('Cached UrlElementTree data is invalid');
            }

            // Simply add all models, validation already done upon inserting data into cache
            foreach ($data as $urlElement) {
                $tree->add($urlElement, true); // No duplication is allowed here
                $counter++;
            }
        } catch (Throwable $e) {
            $this->cache->delete(self::CACHE_KEY);
            LoggerHelper::logRawException($this->logger, $e);

            return false;
        }

        $this->logger->debug('Added :count URL elements to tree from cache', [':count' => $counter]);

        return true;
    }

    /**
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     *
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function storeInCache(UrlElementTreeInterface $tree): void
    {
        $data = [];

        // Get models in the order when the parent iface is always populated before child
        foreach ($tree->getRecursiveIteratorIterator() as $model) {
            $data[] = $model;
        }

        $this->logger->debug('Storing :count URL elements in cache', [
            ':count' => count($data),
        ]);

        $this->cache->set(self::CACHE_KEY, serialize($data), 86400); // 1 day
    }
}
