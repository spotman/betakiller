<?php
namespace BetaKiller\Assets;

use BetaKiller\Assets\Provider\AbstractAssetsProvider;
use BetaKiller\Assets\Provider\AbstractAssetsProviderImage;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Assets\Storage\AssetsStorageInterface;
use BetaKiller\Assets\UrlStrategy\AssetsUrlStrategyInterface;
use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Factory\NamespaceBasedFactory;
use BetaKiller\Repository\RepositoryInterface;
use BetaKiller\Utils\Instance\SingletonTrait;
use Spotman\Acl\Resource\ResolvingResourceInterface;

/**
 * Class AssetsProviderFactory
 *
 * @package BetaKiller\Assets
 */
class AssetsProviderFactory
{
    use SingletonTrait;

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
     * @param \BetaKiller\Config\ConfigProviderInterface $config
     * @param \BetaKiller\Factory\NamespaceBasedFactory  $factory
     */
    public function __construct(ConfigProviderInterface $config, NamespaceBasedFactory $factory)
    {
        $this->config  = $config;
        $this->factory = $factory
            ->setClassPrefixes('Assets', 'Provider')
            ->setExpectedInterface(AssetsProviderInterface::class);
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
     * @return AbstractAssetsProvider|AbstractAssetsProviderImage|AssetsProviderInterface|mixed
     */
    public function createFromModelCodename(string $modelName)
    {
        if ($cached = $this->instances[$modelName] ?? null) {
            return $cached;
        }

        $modelConfig     = $this->getModelConfig($modelName);
        $providerName    = $modelConfig[AbstractAssetsProvider::CONFIG_MODEL_PROVIDER_KEY];
        $storageConfig   = $modelConfig[AbstractAssetsProvider::CONFIG_MODEL_STORAGE_KEY];
        $urlStrategyName = $modelConfig[AbstractAssetsProvider::CONFIG_MODEL_URL_STRATEGY_KEY] ?? 'Hash';

        // Repository codename is equal model name
        $repository = $this->repositoryFactory->create($modelName);

        // Acl resource name is equal to model name
        $aclResource = $this->acl->getResource($modelName);

        if (!($aclResource instanceof ResolvingResourceInterface)) {
            throw new AssetsException('Acl resource :name must implement :must', [
                ':name' => $modelName,
                ':must' => ResolvingResourceInterface::class,
            ]);
        }

        $storageInstance = $this->createStorageFromConfig($storageConfig);

        $urlStrategyInstance = $this->urlStrategyFactory->create($urlStrategyName);

        // TODO Collect custom processing rules

        $providerInstance = $this->create($providerName, $repository, $storageInstance, $aclResource, $urlStrategyInstance);

        $this->instances[$modelName] = $providerInstance;

        return $providerInstance;
    }

    public function create(
        string $codename,
        RepositoryInterface $repository,
        AssetsStorageInterface $storage,
        ResolvingResourceInterface $aclResource,
        AssetsUrlStrategyInterface $urlStrategy
    ): AssetsProviderInterface {
        /** @var \BetaKiller\Assets\Provider\AssetsProviderInterface $instance */
        $instance = $this->factory->create($codename, [
            'storage'     => $storage,
            'repository'  => $repository,
            'aclResource' => $aclResource,
            'urlStrategy' => $urlStrategy,
        ]);

        $instance->setCodename($codename);

        return $instance;
    }

    /**
     * @param array $storageConfig
     *
     * @return \BetaKiller\Assets\Storage\AssetsStorageInterface
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
