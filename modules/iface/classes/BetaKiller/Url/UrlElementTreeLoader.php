<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\IFace\Exception\UrlElementException;
use BetaKiller\Url\ModelProvider\UrlElementProviderDatabase;
use BetaKiller\Url\ModelProvider\UrlElementProviderXmlConfig;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class UrlElementTreeLoader
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Url\ModelProvider\UrlElementProviderDatabase
     */
    private $databaseProvider;

    /**
     * @var \BetaKiller\Url\ModelProvider\UrlElementProviderXmlConfig
     */
    private $xmlProvider;

    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    private $cache;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * Initializer constructor.
     *
     * @param \BetaKiller\Url\ModelProvider\UrlElementProviderDatabase  $databaseProvider
     * @param \BetaKiller\Url\ModelProvider\UrlElementProviderXmlConfig $xmlProvider
     * @param \BetaKiller\Helper\AppEnvInterface                        $appEnv
     * @param \Psr\SimpleCache\CacheInterface                           $cache
     * @param \Psr\Log\LoggerInterface                                  $logger
     */
    public function __construct(
        UrlElementProviderDatabase $databaseProvider,
        UrlElementProviderXmlConfig $xmlProvider,
        AppEnvInterface $appEnv,
        CacheInterface $cache,
        LoggerInterface $logger
    ) {
        $this->databaseProvider = $databaseProvider;
        $this->xmlProvider      = $xmlProvider;
        $this->cache            = $cache;
        $this->logger           = $logger;
        $this->appEnv           = $appEnv;
    }

    /**
     * @return \BetaKiller\Url\UrlElementTreeInterface
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function load(): UrlElementTreeInterface
    {
        $key        = 'ifaceModelTree';
        $this->tree = $this->factory();

        if (!$this->loadTreeFromCache($key)) {
            $this->loadTreeFromProviders();
            $this->storeTreeInCache($key);
        }

        return $this->tree;
    }

    private function factory(): UrlElementTreeInterface
    {
        return new UrlElementTree();
    }

    /**
     * @param string $key
     *
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function loadTreeFromCache(string $key): bool
    {
        $serializedModels = $this->cache->get($key);

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
                $this->tree->add($urlElement, true); // No duplication is allowed here
                $counter++;
            }
        } catch (\Throwable $e) {
            $this->cache->delete($key);
            $this->logException($this->logger, $e);

            return false;
        }

        $this->logger->debug('Added :count URL elements to tree from cache', [':count' => $counter]);

        return true;
    }

    /**
     * @param string $key
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    private function storeTreeInCache(string $key): void
    {
        $data = [];

        // Get models in the order when the parent iface is always populated before child
        foreach ($this->tree->getRecursiveIteratorIterator() as $model) {
            $data[] = $model;
        }

        $this->logger->debug('Storing :count URL elements in cache', [':count' => \count($data)]);

        $this->cache->set($key, serialize($data), 86400); // 1 day
    }

    /**
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    private function loadTreeFromProviders(): void
    {
        $this->logger->debug('Loading URL elements tree from providers');

        /** @var \BetaKiller\Url\ModelProvider\UrlElementProviderInterface[] $sources */
        $sources = [
            $this->xmlProvider,
        ];

        // TODO Remove this hack after resolving spotman/betakiller#35
        if (!$this->appEnv->inTestingMode()) {
            $sources[] = $this->databaseProvider;
        }

        foreach ($sources as $provider) {
            foreach ($provider->getAll() as $urlElement) {
                $this->tree->add($urlElement); // Allow overwriting
            }
        }

        $this->tree->validate();
    }
}
