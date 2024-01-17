<?php
namespace BetaKiller\Cache;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Exception;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\ProxyAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;

class SymfonyCacheProvider extends ProxyAdapter
{
    /**
     * @var string
     */
    private string $ns;

    /**
     * @var int
     */
    private int $ttl;

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
        $nsPrefix = $appEnv->isAppRunning()
            ? implode('.', [$appEnv->getAppCodename(), $appEnv->getModeName(), $appEnv->getRevisionKey()])
            : 'core';

        $this->ns = $nsPrefix.'.default';

        $settings = (array)$config->load('cache', ['default']);

        if (!$settings) {
            throw new Exception('App-related cache config is absent');
        }

        $this->ttl = (int)$settings['expire'];
        unset($settings['expire']);

        // Create app-related cache adapter
        $pool = $this->createPool($settings);

        parent::__construct($pool);
    }

    private function createPool(array $settings): CacheItemPoolInterface
    {
        $adapterName = mb_strtolower($settings['adapter']);

        switch ($adapterName) {
            case 'array':
                return new ArrayAdapter($this->ttl);

            case 'redis':
                $host = $settings['host'];
                $port = $settings['port'];

                $uri = sprintf('redis://%s:%d?timeout=5', $host, $port);

                $client = RedisAdapter::createConnection($uri);

                return new RedisTagAwareAdapter($client, $this->ns, $this->ttl);

            default:
                throw new \LogicException(sprintf('Unknown cache adapter "%s"', $adapterName));
        }
    }
}
