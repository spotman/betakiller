<?php
namespace BetaKiller\Cache;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Exception;
use Doctrine\Common\Cache\ChainCache;
use Pcelta\Doctrine\Cache\Factory;

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
     * @param \BetaKiller\Env\AppEnvInterface            $appEnv
     * @param \BetaKiller\Config\ConfigProviderInterface $config
     *
     * @throws \BetaKiller\Exception
     */
    public function __construct(AppEnvInterface $appEnv, ConfigProviderInterface $config)
    {
        $this->nsPrefix = $appEnv->isAppRunning()
            ? implode('.', [$appEnv->getAppCodename(), $appEnv->getModeName(), $appEnv->getRevisionKey()])
            : 'core';

        $settings = (array)$config->load(['cache', 'default']);

        if (!$settings) {
            throw new Exception('App-related cache config is absent');
        }

        $this->defaultExpire = (int)$settings['expire'];
        unset($settings['expire']);

        // Alias for simplicity
        $settings['adapter_name'] = $settings['adapter'];

        $providers = [];

// Prevent memory leaks in daemons
//        if ($settings['adapter'] !== 'Array') {
//            // Basic per-request in-memory implementation for better performance
//            $providers[] = new ArrayCache();
//        }

        // Add app-related cache adapter
        $providers[] = (new Factory())->create($settings);

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
