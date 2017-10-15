<?php
namespace BetaKiller\Assets;

use BetaKiller\Acl\Resource\AssetsAclResourceInterface;
use BetaKiller\Assets\Provider\AbstractAssetsProvider;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Assets\Storage\AssetsStorageInterface;
use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Factory\NamespaceBasedFactory;
use BetaKiller\Factory\RepositoryFactory;
use Spotman\Acl\AclInterface;

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
    private $config;

    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * @var \BetaKiller\Factory\RepositoryFactory
     */
    private $repositoryFactory;

    /**
     * @var \BetaKiller\Assets\AssetsStorageFactory
     */
    private $storageFactory;

    /**
     * @var \BetaKiller\Assets\AssetsUrlStrategyFactory
     */
    private $urlStrategyFactory;

    /**
     * @var \BetaKiller\Assets\AssetsHandlerFactory
     */
    private $handlerFactory;

    /**
     * @var \Spotman\Acl\AclInterface
     */
    private $acl;

    /**
     * @var AssetsProviderInterface[]
     */
    private $instances;

    /**
     * AssetsProviderFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactory   $factory
     * @param \BetaKiller\Config\ConfigProviderInterface  $config
     * @param \BetaKiller\Factory\RepositoryFactory       $repositoryFactory
     * @param \BetaKiller\Assets\AssetsStorageFactory     $storageFactory
     * @param \BetaKiller\Assets\AssetsUrlStrategyFactory $urlStrategyFactory
     * @param \BetaKiller\Assets\AssetsHandlerFactory     $handlerFactory
     * @param \Spotman\Acl\AclInterface                   $acl
     */
    public function __construct(
        NamespaceBasedFactory $factory,
        ConfigProviderInterface $config,
        RepositoryFactory $repositoryFactory,
        AssetsStorageFactory $storageFactory,
        AssetsUrlStrategyFactory $urlStrategyFactory,
        AssetsHandlerFactory $handlerFactory,
        AclInterface $acl
    ) {
        $this->factory = $factory
            ->setClassPrefixes('Assets', 'Provider')
            ->setClassSuffix('AssetsProvider')
            ->setExpectedInterface(AssetsProviderInterface::class);

        $this->config             = $config;
        $this->repositoryFactory  = $repositoryFactory;
        $this->storageFactory     = $storageFactory;
        $this->urlStrategyFactory = $urlStrategyFactory;
        $this->handlerFactory     = $handlerFactory;
        $this->acl                = $acl;
    }

    public function createFromUrlKey($key)
    {
        // Try to find provider by url key
        $codename = $this->getModelCodenameByUrlKey($key);

        // If nothing was found, then url key is a codename (kept for BC)
        if (!$codename) {
            $codename = $key;
        }

        return $this->createFromModelCodename($codename);
    }

    private function getModelCodenameByUrlKey($key)
    {
        $providersConfig = $this->config->load([
            AbstractAssetsProvider::CONFIG_KEY,
            AbstractAssetsProvider::CONFIG_PROVIDERS_KEY,
        ]);

        $keyName = AbstractAssetsProvider::CONFIG_MODEL_URL_KEY;

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
            AbstractAssetsProvider::CONFIG_KEY,
            AbstractAssetsProvider::CONFIG_PROVIDERS_KEY,
            $modelName,
        ]);
    }

    /**
     * @param string $storageName
     *
     * @return array
     * @TODO Move to StorageFactory
     */
    private function getStorageDefaultConfig(string $storageName): array
    {
        return $this->config->load([
            AbstractAssetsProvider::CONFIG_KEY,
            AbstractAssetsProvider::CONFIG_STORAGES_KEY,
            $storageName,
        ]);
    }

    /**
     * Factory method
     *
     * @param string $modelName
     *
     * @return \BetaKiller\Assets\Provider\ImageAssetsProviderInterface|AssetsProviderInterface|mixed
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Assets\AssetsException
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function createFromModelCodename(string $modelName)
    {
        if ($cached = $this->instances[$modelName] ?? null) {
            return $cached;
        }

        $modelConfig             = $this->getModelConfig($modelName);
        $providerName            = $modelConfig[AbstractAssetsProvider::CONFIG_MODEL_PROVIDER_KEY];
        $storageConfig           = $modelConfig[AbstractAssetsProvider::CONFIG_MODEL_STORAGE_KEY];
        $urlStrategyName         = $modelConfig[AbstractAssetsProvider::CONFIG_MODEL_URL_STRATEGY_KEY] ?? 'Hash';
        $postUploadHandlersNames = $modelConfig[AbstractAssetsProvider::CONFIG_MODEL_POST_UPLOAD_KEY] ?? [];

        // Repository codename is equal model name
        $repository = $this->repositoryFactory->create($modelName);

        // Acl resource name is equal to model name
        $aclResource = $this->acl->getResource($modelName);

        if (!($aclResource instanceof AssetsAclResourceInterface)) {
            throw new AssetsException('Acl resource :name must implement :must', [
                ':name' => $modelName,
                ':must' => AssetsAclResourceInterface::class,
            ]);
        }

        $storage = $this->createStorageFromConfig($storageConfig);

        $urlStrategy = $this->urlStrategyFactory->create($urlStrategyName, $repository);

        /** @var \BetaKiller\Assets\Provider\AssetsProviderInterface $providerInstance */
        $providerInstance = $this->factory->create($providerName, [
            'storage'     => $storage,
            'repository'  => $repository,
            'aclResource' => $aclResource,
            'urlStrategy' => $urlStrategy,
        ]);

        // Store codename for future use
        $providerInstance->setCodename($modelName);

        // Inject custom post upload handlers
        foreach ($postUploadHandlersNames as $handlerName) {
            $handler = $this->handlerFactory->create($handlerName);
            $providerInstance->addPostUploadHandler($handler);
        }

        $this->instances[$modelName] = $providerInstance;

        return $providerInstance;
    }

    /**
     * @param array $storageConfig
     *
     * @return \BetaKiller\Assets\Storage\AssetsStorageInterface
     * @throws \BetaKiller\Assets\AssetsStorageException
     * @TODO Move to StorageFactory
     */
    private function createStorageFromConfig(array $storageConfig): AssetsStorageInterface
    {
        $storageName = $storageConfig[AbstractAssetsProvider::CONFIG_MODEL_STORAGE_NAME_KEY];

        $defaultStorageConfig = $this->getStorageDefaultConfig($storageName);

        $storageConfig = array_merge($defaultStorageConfig, $storageConfig);

        $relativePath = $storageConfig[AbstractAssetsProvider::CONFIG_MODEL_STORAGE_PATH_KEY];
        $basePath     = $storageConfig[AbstractAssetsProvider::CONFIG_STORAGE_BASE_PATH_KEY];

        $instance = $this->storageFactory->create($storageName);

        $instance->setBasePath($basePath.DIRECTORY_SEPARATOR.$relativePath);

        return $instance;
    }
}
