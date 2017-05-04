<?php namespace BetaKiller\Assets;

use BetaKiller\Assets\Provider\AbstractAssetsProvider;
use BetaKiller\Assets\Provider\AbstractAssetsProviderImage;

use BetaKiller\Utils\Instance\SingletonTrait;
use BetaKiller\Utils\Factory\BaseFactoryTrait;
use BetaKiller\DI\ContainerTrait;
use BetaKiller\Config\ConfigInterface;

/**
 * Class AssetsProviderFactory
 *
 * @todo Refactoring to NamespaceBasedFactory
 *
 * @package BetaKiller\Assets
 */
class AssetsProviderFactory
{
    use SingletonTrait,
        BaseFactoryTrait,
        ContainerTrait;

    /**
     * @var \BetaKiller\Config\ConfigInterface
     */
    private $config;

    /**
     * AssetsProviderFactory constructor.
     *
     * @param \BetaKiller\Config\ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function createFromUrlKey($key)
    {
        // Try to find provider by url key
        $codename = $this->getCodenameByUrlKey($key);

        // If nothing was found, then url key is a codename (kept for BC)
        if (!$codename) {
            $codename = $key;
        }

        return $this->create($codename);
    }

    private function getCodenameByUrlKey($key)
    {
        $providersConfig = $this->config->load([
            AbstractAssetsProvider::CONFIG_KEY,
            AbstractAssetsProvider::CONFIG_PROVIDERS_KEY,
        ]);
        $keyName = AbstractAssetsProvider::CONFIG_URL_KEY;

        foreach ($providersConfig as $codename => $data) {
            $providerKey = isset($data[$keyName]) ? $data[$keyName] : null;

            if ($providerKey && $providerKey === $key) {
                return $codename;
            }
        }

        return null;
    }

    /**
     * Factory method
     *
     * @param $name
     *
     * @return AbstractAssetsProvider|AbstractAssetsProviderImage
     */
    public function create($name)
    {
        return $this->_create($name);
    }

    /**
     * @param \BetaKiller\Assets\Provider\AbstractAssetsProvider $instance
     * @param                                                    $codename
     */
    protected function store_codename($instance, $codename)
    {
        $instance->setCodename($codename);
    }

    protected function make_instance_class_name($name)
    {
        return '\\Assets_Provider_'.$name;
    }

    protected function make_instance($class_name, ...$parameters)
    {
        return $this->getContainer()->make($class_name, $parameters);
    }
}
