<?php
namespace BetaKiller\Assets;

use BetaKiller\Acl\Resource\AssetsAclResourceInterface;
use BetaKiller\Assets\Provider\AbstractAssetsProvider;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryBuilder;
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
     * @var \BetaKiller\Assets\AssetsPathStrategyFactory
     */
    private $pathStrategyFactory;

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
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     * @param \BetaKiller\Config\ConfigProviderInterface       $config
     * @param \BetaKiller\Factory\RepositoryFactory            $repositoryFactory
     * @param \BetaKiller\Assets\AssetsStorageFactory          $storageFactory
     * @param \BetaKiller\Assets\AssetsPathStrategyFactory     $pathStrategyFactory
     * @param \BetaKiller\Assets\AssetsHandlerFactory          $handlerFactory
     * @param \Spotman\Acl\AclInterface                        $acl
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(
        NamespaceBasedFactoryBuilder $factoryBuilder,
        ConfigProviderInterface $config,
        RepositoryFactory $repositoryFactory,
        AssetsStorageFactory $storageFactory,
        AssetsPathStrategyFactory $pathStrategyFactory,
        AssetsHandlerFactory $handlerFactory,
        AclInterface $acl
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
        $this->acl                 = $acl;
    }

    /**
     * @param string $key
     *
     * @return \BetaKiller\Assets\Provider\AssetsProviderInterface|\BetaKiller\Assets\Provider\ImageAssetsProviderInterface|mixed
     * @throws \BetaKiller\Assets\AssetsProviderException
     * @throws \BetaKiller\Assets\AssetsException
     * @throws \BetaKiller\Assets\AssetsStorageException
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

        return $this->createFromModelCodename($codename);
    }

    private function getModelCodenameByUrlKey($key)
    {
        $providersConfig = $this->config->load([
            AbstractAssetsProvider::CONFIG_KEY,
            AbstractAssetsProvider::CONFIG_MODELS_KEY,
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
            AbstractAssetsProvider::CONFIG_MODELS_KEY,
            $modelName,
        ]);
    }

    /**
     * Factory method
     *
     * @param string $modelName
     *
     * @return \BetaKiller\Assets\Provider\ImageAssetsProviderInterface|AssetsProviderInterface|mixed
     * @throws \BetaKiller\Assets\AssetsProviderException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Assets\AssetsException
     * @throws \BetaKiller\Assets\AssetsStorageException
     */
    public function createFromModelCodename(string $modelName)
    {
        $cached = $this->instances[$modelName] ?? null;

        if ($cached) {
            return $cached;
        }

        $modelConfig             = $this->getModelConfig($modelName);
        $providerName            = $modelConfig[AbstractAssetsProvider::CONFIG_MODEL_PROVIDER_KEY];
        $storageConfig           = $modelConfig[AbstractAssetsProvider::CONFIG_MODEL_STORAGE_KEY];
        $pathStrategyName        = $modelConfig[AbstractAssetsProvider::CONFIG_MODEL_PATH_STRATEGY_KEY] ?? 'MultiLevelHash';
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

        $storage = $this->storageFactory->createFromConfig($storageConfig);

        $pathStrategy = $this->pathStrategyFactory->create($pathStrategyName, $repository);

        /** @var \BetaKiller\Assets\Provider\AssetsProviderInterface $providerInstance */
        $providerInstance = $this->factory->create($providerName, [
            'storage'      => $storage,
            'repository'   => $repository,
            'aclResource'  => $aclResource,
            'pathStrategy' => $pathStrategy,
        ]);

        // Check provider => storage matrix
        if ($providerInstance->isProtected() && $storage->isPublic()) {
            throw new AssetsProviderException('Protected assets provider :name must have protected storage', [
                ':name' => $modelName,
            ]);
        }

        // Public provider url key and public storage path must be the same to prevent collisions
        if (!$providerInstance->isProtected() && $storage->isPublic()) {
            $storagePathKey = $storageConfig[AbstractAssetsProvider::CONFIG_MODEL_STORAGE_PATH_KEY];
            $providerUrlKey = $modelConfig[AbstractAssetsProvider::CONFIG_MODEL_URL_KEY];

            if ($providerUrlKey !== $storagePathKey) {
                throw new AssetsProviderException('Public provider :name url key and storage path must be the same', [
                    ':name' => $modelName,
                ]);
            }
        }

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
}
