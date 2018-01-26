<?php
declare(strict_types=1);

namespace BetaKiller\IFace;


use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\ModelProvider\IFaceModelProviderDatabase;
use BetaKiller\IFace\ModelProvider\IFaceModelProviderXmlConfig;
use BetaKiller\ModuleInitializerInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class Initializer implements ModuleInitializerInterface
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\IFace\ModelProvider\IFaceModelProviderDatabase
     */
    private $databaseProvider;

    /**
     * @var \BetaKiller\IFace\ModelProvider\IFaceModelProviderXmlConfig
     */
    private $xmlProvider;

    /**
     * @var \BetaKiller\IFace\IFaceModelTree
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
     * Initializer constructor.
     *
     * @param \BetaKiller\IFace\ModelProvider\IFaceModelProviderDatabase  $databaseProvider
     * @param \BetaKiller\IFace\ModelProvider\IFaceModelProviderXmlConfig $xmlProvider
     * @param \BetaKiller\IFace\IFaceModelTree                            $tree
     * @param \Psr\SimpleCache\CacheInterface                             $cache
     * @param \Psr\Log\LoggerInterface                                    $logger
     */
    public function __construct(
        IFaceModelProviderDatabase $databaseProvider,
        IFaceModelProviderXmlConfig $xmlProvider,
        IFaceModelTree $tree,
        CacheInterface $cache,
        LoggerInterface $logger
    ) {
        $this->databaseProvider = $databaseProvider;
        $this->xmlProvider      = $xmlProvider;
        $this->tree             = $tree;
        $this->cache            = $cache;
        $this->logger           = $logger;
    }

    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function init(): void
    {
        $key = 'ifaceModelTree';

        if (!$this->loadTreeFromCache($key)) {
            $this->loadTreeFromProviders();
            $this->storeTreeInCache($key);
        }
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

        $this->logger->debug('Loading IFace tree from cache');

        try {
            $data = unserialize($serializedModels, [IFaceModelInterface::class]);

            if (!$data || !\is_array($data)) {
                throw new IFaceException('Cached IFaceModelTree data is invalid');
            }

            $counter = 0;

            // Simply add all models, validation already done upon inserting data into cache
            foreach ($data as $model) {
                $this->tree->add($model, true); // No duplication is allowed here
                $counter++;
            }

            $this->logger->debug('Added :count IFaces to tree from cache', [':count' => $counter]);
        } catch (\Throwable $e) {
            $this->cache->delete($key);
            $this->logException($this->logger, $e);
            return false;
        }

        return true;
    }

    /**
     * @param string $key
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function storeTreeInCache(string $key): void
    {
        $data = [];

        // Get models in the order when the parent iface is always populated before child
        foreach ($this->tree->getRecursiveIterator() as $model) {
            $data[] = $model;
        }

        $this->cache->set($key, serialize($data));
    }

    /**
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function loadTreeFromProviders(): void
    {
        $this->logger->debug('Loading IFace tree from providers');

        /** @var \BetaKiller\IFace\ModelProvider\IFaceModelProviderInterface[] $sources */
        $sources = [
            $this->xmlProvider,
            $this->databaseProvider,
        ];

        foreach ($sources as $provider) {
            foreach ($provider->getAll() as $ifaceModel) {
                $this->tree->add($ifaceModel); // Allow overwriting
            }
        }

        $this->tree->validate();
    }
}
