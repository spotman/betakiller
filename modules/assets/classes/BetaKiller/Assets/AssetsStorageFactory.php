<?php
namespace BetaKiller\Assets;

use BetaKiller\Assets\Storage\AssetsStorageInterface;
use BetaKiller\Config\AssetsConfig;
use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryInterface;

class AssetsStorageFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    /**
     * @var \BetaKiller\Config\ConfigProviderInterface
     */
    private ConfigProviderInterface $config;

    /**
     * AssetsStorageFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $factoryBuilder
     * @param \BetaKiller\Config\ConfigProviderInterface                $config
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilderInterface $factoryBuilder, ConfigProviderInterface $config)
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
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createFromConfig(array $config): AssetsStorageInterface
    {
        $storageName = $config[AssetsConfig::CONFIG_MODEL_STORAGE_NAME_KEY];

        $defaultStorageConfig = $this->getStorageDefaultConfig($storageName);

        if ($defaultStorageConfig) {
            $config = array_merge($defaultStorageConfig, $config);
        }

        $relativePath = $config[AssetsConfig::CONFIG_MODEL_STORAGE_PATH_KEY];

        $instance = $this->create($storageName);

        $instance->setBasePath($relativePath);

        return $instance;
    }

    /**
     * @param string $storageName
     *
     * @return array
     */
    private function getStorageDefaultConfig(string $storageName): ?array
    {
        return $this->config->load(AssetsConfig::CONFIG_KEY,[
            AssetsConfig::CONFIG_STORAGES_KEY,
            $storageName,
        ]);
    }
}
