<?php
namespace BetaKiller\Assets;

use BetaKiller\Assets\Exception\AssetsProviderException;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryInterface;
use BetaKiller\Factory\RepositoryFactoryInterface;

/**
 * Class AssetsProviderFactory
 *
 * @package BetaKiller\Assets
 */
class AssetsProviderFactory
{
    /**
     * @var \BetaKiller\Config\ConfigProviderInterface
     */
    private ConfigProviderInterface $config;

    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    /**
     * @var \BetaKiller\Factory\RepositoryFactoryInterface
     */
    private RepositoryFactoryInterface $repositoryFactory;

    /**
     * @var \BetaKiller\Assets\AssetsStorageFactory
     */
    private $storageFactory;

    /**
     * @var \BetaKiller\Assets\AssetsPathStrategyFactory
     */
    private $pathStrategyFactory;

    /**
     * @var \BetaKiller\Assets\AssetsHandlerFactory
     */
    private $handlerFactory;

    /**
     * @var AssetsProviderInterface[]
     */
    private $instances;

    /**
     * AssetsProviderFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $factoryBuilder
     * @param \BetaKiller\Config\ConfigProviderInterface                $config
     * @param \BetaKiller\Factory\RepositoryFactoryInterface            $repositoryFactory
     * @param \BetaKiller\Assets\AssetsStorageFactory                   $storageFactory
     * @param \BetaKiller\Assets\AssetsPathStrategyFactory              $pathStrategyFactory
     * @param \BetaKiller\Assets\AssetsHandlerFactory                   $handlerFactory
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(
        NamespaceBasedFactoryBuilderInterface $factoryBuilder,
        ConfigProviderInterface $config,
        RepositoryFactoryInterface $repositoryFactory,
        AssetsStorageFactory $storageFactory,
        AssetsPathStrategyFactory $pathStrategyFactory,
        AssetsHandlerFactory $handlerFactory
    ) {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->setClassNamespaces('Assets', 'Provider')
            ->setClassSuffix('AssetsProvider')
            ->setExpectedInterface(AssetsProviderInterface::class);

        $this->config              = $config;
        $this->repositoryFactory   = $repositoryFactory;
        $this->storageFactory      = $storageFactory;
        $this->pathStrategyFactory = $pathStrategyFactory;
        $this->handlerFactory      = $handlerFactory;
    }

    /**
     * @param string $key
     *
     * @return \BetaKiller\Assets\Provider\AssetsProviderInterface|\BetaKiller\Assets\Provider\ImageAssetsProviderInterface|mixed
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createFromUrlKey(string $key)
    {
        // Try to find provider by url key
        $codename = $this->getModelCodenameByUrlKey($key);

        // If nothing was found, then url key is a codename (kept for BC)
        if (!$codename) {
            $codename = $key;
        }

        return $this->createFromCodename($codename);
    }

    private function getModelCodenameByUrlKey(string $key)
    {
        $providersConfig = $this->config->load([
            AssetsConfig::CONFIG_KEY,
            AssetsConfig::CONFIG_MODELS_KEY,
        ]);

        $keyName = AssetsConfig::CONFIG_MODEL_URL_KEY;

        foreach ($providersConfig as $codename => $data) {
            $providerKey = $data[$keyName] ?? null;

            if ($providerKey && $providerKey === $key) {
                return $codename;
            }
        }

        return null;
    }

    private function getModelConfig(string $modelName): array
    {
        return $this->config->load([
            AssetsConfig::CONFIG_KEY,
            AssetsConfig::CONFIG_MODELS_KEY,
            $modelName,
        ]);
    }

    /**
     * Factory method
     *
     * @param string $modelName
     *
     * @return \BetaKiller\Assets\Provider\ImageAssetsProviderInterface|AssetsProviderInterface|mixed
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     */
    public function createFromCodename(string $modelName)
    {
        $cached = $this->instances[$modelName] ?? null;

        if ($cached) {
            return $cached;
        }

        $modelConfig             = $this->getModelConfig($modelName);
        $providerName            = $modelConfig[AssetsConfig::CONFIG_MODEL_PROVIDER_KEY];
        $storageConfig           = $modelConfig[AssetsConfig::CONFIG_MODEL_STORAGE_KEY];
        $pathStrategyName        = $modelConfig[AssetsConfig::CONFIG_MODEL_PATH_STRATEGY_KEY] ?? 'MultiLevelHash';
        $postUploadHandlersNames = $modelConfig[AssetsConfig::CONFIG_MODEL_POST_UPLOAD_KEY] ?? [];

        // Repository codename is equal model name
        $repository = $this->repositoryFactory->create($modelName);

        $storage = $this->storageFactory->createFromConfig($storageConfig);

        $pathStrategy = $this->pathStrategyFactory->create($pathStrategyName, $repository);

        /** @var \BetaKiller\Assets\Provider\AssetsProviderInterface $providerInstance */
        $providerInstance = $this->factory->create($providerName, [
            'storage'      => $storage,
            'repository'   => $repository,
            'pathStrategy' => $pathStrategy,
        ]);

        // Store codename for future use
        $providerInstance->setCodename($modelName);

        // Check provider => storage matrix
        if ($providerInstance->isProtected() && $storage->isInsideDocRoot()) {
            throw new AssetsProviderException('Protected assets provider :name must have protected storage', [
                ':name' => $modelName,
            ]);
        }

        // Public provider url key and public storage path must be the same to prevent collisions
        if (!$providerInstance->isProtected() && $storage->isInsideDocRoot()) {
            $storagePathKey = $storageConfig[AssetsConfig::CONFIG_MODEL_STORAGE_PATH_KEY];
            $providerUrlKey = $modelConfig[AssetsConfig::CONFIG_MODEL_URL_KEY];

            if ($providerUrlKey !== $storagePathKey) {
                throw new AssetsProviderException('Public provider :name url key and storage path must be the same', [
                    ':name' => $modelName,
                ]);
            }
        }

        // Inject custom post upload handlers
        foreach ($postUploadHandlersNames as $handlerName) {
            $handler = $this->handlerFactory->create($handlerName);
            $providerInstance->addPostUploadHandler($handler);
        }

        $this->instances[$modelName] = $providerInstance;

        return $providerInstance;
    }
}
