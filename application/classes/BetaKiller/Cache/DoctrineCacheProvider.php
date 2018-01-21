<?php
namespace BetaKiller\Cache;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Exception;
use MultiSite;
use BetaKiller\Helper\AppEnv;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Pcelta\Doctrine\Cache\Factory as CacheFactory;

class DoctrineCacheProvider extends ChainCache
{
    /**
     * @var string
     */
    private $nsPrefix;

    /**
     * @var int
     */
    private $defaultExpire;

    /**
     * DoctrineCacheProvider constructor.
     *
     * @param \MultiSite                                 $multiSite
     * @param \BetaKiller\Helper\AppEnv                  $appEnv
     * @param \BetaKiller\Config\ConfigProviderInterface $config
     *
     * @throws \Pcelta\Doctrine\Cache\Exception\InvalidCacheConfig
     * @throws \BetaKiller\Exception
     */
    public function __construct(MultiSite $multiSite, AppEnv $appEnv, ConfigProviderInterface $config)
    {
        $workingName = $multiSite->getWorkingName();

        $this->nsPrefix = implode('.', [$workingName ?: 'core', $appEnv->getModeName()]);

        $settings = $config->load(['cache', 'default']);

        if (!$settings) {
            throw new Exception('App-related cache config is absent');
        }

        $this->defaultExpire = (int)$settings['expire'];
        unset($settings['expire']);

        // Alias for simplicity
        $settings['adapter_name'] = $settings['adapter'];

        $providers = [];

        if ($settings['adapter'] !== 'Array') {
            // Basic per-request in-memory implementation for better performance
            $providers[] = new ArrayCache();
        }

        // Add app-related cache adapter
        $factory = new CacheFactory();
        $providers[] = $factory->create($settings);

        parent::__construct($providers);

        // Preset default namespace
        $this->setNamespace('default');
    }

    /**
     * {@inheritDoc}
     */
    public function setNamespace($namespace): void
    {
        parent::setNamespace($this->nsPrefix.'.'.$namespace);
    }

    /**
     * {@inheritDoc}
     */
    protected function doSave($id, $data, $lifeTime = 0): bool
    {
        return parent::doSave($id, $data, $lifeTime ?: $this->defaultExpire);
    }
}
