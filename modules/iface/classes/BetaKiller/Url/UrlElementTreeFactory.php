<?php

declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Dev\StartupProfiler;
use BetaKiller\Factory\UrlElementFactoryInterface;
use BetaKiller\Helper\LoggerHelper;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Throwable;

use function count;
use function is_array;

final readonly class UrlElementTreeFactory
{
    private const CACHE_KEY = 'UrlElementTree.models';

    public function __construct(
        private UrlElementTreeLoader $loader,
        private UrlElementTreeValidatorInterface $validator,
        private UrlElementFactoryInterface $urlElementFactory,
        private CacheInterface $cache,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(): UrlElementTreeInterface
    {
        $tree = new UrlElementTree();

        $p = StartupProfiler::begin('UrlElementTree loading');

        if (!$this->loadFromCache($tree)) {
            // Fetch from loader
            $this->loader->loadInto($tree);

            // Check for errors
            $this->validator->validate($tree);

            $this->storeInCache($tree);
        }

        StartupProfiler::end($p);

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
        $serializedData = $this->cache->get(self::CACHE_KEY);

        if (!$serializedData) {
            return false;
        }

        $this->logger->debug('Loading URL elements tree from cache');
        $counter = 0;

        try {
            $models = $this->unpackModels($serializedData);

            // Simply add all models, validation already done upon inserting data into cache
            foreach ($models as $urlElement) {
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
        $models = [];

        // Get models in the order when the parent iface is always populated before child
        foreach ($tree->getRecursiveIteratorIterator() as $model) {
            $models[] = $model;
        }

        $this->logger->debug('Storing :count URL elements in cache', [
            ':count' => count($models),
        ]);

        $this->cache->set(self::CACHE_KEY, $this->packModels($models), 86400); // 1 day
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface[] $models
     *
     * @return string
     */
    private function packModels(array $models): string
    {
        $data = [];

        foreach ($models as $model) {
            $tag = $model::getXmlTagName();

            $data[$tag] ??= [];

            $data[$tag][] = $model->asArray();
        }

        return json_encode($data);
    }

    /**
     * @param string $packed
     *
     * @return \BetaKiller\Url\UrlElementInterface[]
     */
    private function unpackModels(string $packed): array
    {
        $data = json_decode($packed, true);

        if (!$data || !is_array($data)) {
            throw new UrlElementException('Cached UrlElementTree data is invalid');
        }

        $models = [];

        foreach ($data as $tag => $configs) {
            foreach ($configs as $config) {
                $models[] = $this->urlElementFactory->createFrom($tag, $config);
            }
        }

        return $models;
    }
}
