<?php
namespace BetaKiller\DI\Container;

use BetaKiller\Config\KohanaConfigProvider;
use DI\ContainerBuilder;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;

class KohanaBasedContainer extends AbstractContainer
{
    /**
     * @return \BetaKiller\DI\ContainerInterface|\DI\Container|mixed
     * @throws \InvalidArgumentException
     * @throws \Kohana_Exception
     */
    protected function containerFactory()
    {
        $builder = new ContainerBuilder();

        $configProvider = new KohanaConfigProvider();

        $config = $configProvider->load(['php-di']);

        $definitions = $config->get('definitions');
        $builder->addDefinitions($definitions);

        $useAutowiring  = $config->get('autowiring', true);
        $useAnnotations = $config->get('annotations', true);

        /** @url http://php-di.org/doc/performances.html */
        $cache = $config->get('cache');

        if ($cache) {
            if (!($cache instanceof Cache)) {
                throw new \InvalidArgumentException('PHP-DI cache must be instance of :type', [
                    ':type' => Cache::class,
                ]);
            }

            if ($cache instanceof CacheProvider) {
                $ns = $config->get('namespace');

                if (!$ns) {
                    throw new \Kohana_Exception('PHP-DI container must have a [namespace] defined in config');
                }

                $cache->setNamespace($ns);
            }

            $builder->setDefinitionCache($cache);
        }

        return $builder
            ->useAutowiring($useAutowiring)
            ->useAnnotations($useAnnotations)
            ->build();
    }
}
