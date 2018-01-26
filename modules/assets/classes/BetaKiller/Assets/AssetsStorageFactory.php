<?php
namespace BetaKiller\Assets;

use BetaKiller\Assets\Provider\AbstractAssetsProvider;
use BetaKiller\Assets\Storage\AssetsStorageInterface;
use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryBuilder;

class AssetsStorageFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * @var \BetaKiller\Config\ConfigProviderInterface
     */
    private $config;

    /**
     * AssetsStorageFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     * @param \BetaKiller\Config\ConfigProviderInterface       $config
     */
    public function __construct(NamespaceBasedFactoryBuilder $factoryBuilder, ConfigProviderInterface $config)
    {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->setClassNamespaces('Assets', 'Storage')
            ->setClassSuffix('AssetsStorage')
            ->setExpectedInterface(AssetsStorageInterface::class);

        $this->config = $config;
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Assets\Storage\AssetsStorageInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(string $codename): AssetsStorageInterface
    {
        return $this->factory->create($codename);
    }

    /**
     * @param array $config
     *
     * @return \BetaKiller\Assets\Storage\AssetsStorageInterface
     * @throws \BetaKiller\Assets\AssetsStorageException
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createFromConfig(array $config): AssetsStorageInterface
    {
        $storageName = $config[AbstractAssetsProvider::CONFIG_MODEL_STORAGE_NAME_KEY];

        $defaultStorageConfig = $this->getStorageDefaultConfig($storageName);

        $config = array_merge($defaultStorageConfig, $config);

        $relativePath = $config[AbstractAssetsProvider::CONFIG_MODEL_STORAGE_PATH_KEY];
        $basePath     = $config[AbstractAssetsProvider::CONFIG_STORAGE_BASE_PATH_KEY];

        $instance = $this->create($storageName);

        $instance->setBasePath($basePath.DIRECTORY_SEPARATOR.$relativePath);

        return $instance;
    }

    /**
     * @param string $storageName
     *
     * @return array
     */
    private function getStorageDefaultConfig(string $storageName): array
    {
        return $this->config->load([
            AbstractAssetsProvider::CONFIG_KEY,
            AbstractAssetsProvider::CONFIG_STORAGES_KEY,
            $storageName,
        ]);
    }
}
