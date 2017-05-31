<?php namespace BetaKiller\Assets;

use BetaKiller\Assets\Provider\AbstractAssetsProvider;
use BetaKiller\Assets\Provider\AbstractAssetsProviderImage;

use BetaKiller\Factory\NamespaceBasedFactory;
use BetaKiller\Utils\Instance\SingletonTrait;
use BetaKiller\Config\ConfigInterface;

/**
 * Class AssetsProviderFactory
 *
 * @package BetaKiller\Assets
 */
class AssetsProviderFactory
{
    use SingletonTrait;

    /**
     * @var \BetaKiller\Config\ConfigInterface
     */
    private $config;

    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * AssetsProviderFactory constructor.
     *
     * @param \BetaKiller\Config\ConfigInterface $config
     * @param \BetaKiller\Factory\NamespaceBasedFactory $factory
     */
    public function __construct(ConfigInterface $config, NamespaceBasedFactory $factory)
    {
        $this->config = $config;
        $this->factory = $factory
            ->setClassPrefixes('Assets', 'Provider')
            ->cacheInstances()
            ->setExpectedInterface(AbstractAssetsProvider::class);
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
     * @param string $codename
     *
     * @return AbstractAssetsProvider|AbstractAssetsProviderImage|mixed
     */
    public function create($codename)
    {
        /** @var AbstractAssetsProvider $instance */
        $instance = $this->factory->create($codename);
        $instance->setCodename($codename);

        return $instance;
    }
}
