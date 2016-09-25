<?php
namespace BetaKiller\DI\Container;

use DI\ContainerBuilder;
use Doctrine\Common\Cache\Cache;

class Kohana extends Base
{
    protected function containerFactory()
    {
        $builder = new ContainerBuilder();

        $config = \Kohana::config('php-di');

        $definitions = $config->get('definitions');
        $builder->addDefinitions($definitions);

        /** @url http://php-di.org/doc/performances.html */
        $cache = $config->get('cache');

        if ( $cache ) {
            if (!($cache instanceof Cache))
                throw new \Kohana_Exception('php-di cache must be instance of :type', [':type' => Cache::class]);

            $cache->setNamespace('kohana-php-di');

            $builder->setDefinitionCache($cache);
//            $builder->writeProxiesToFile(true, 'tmp/proxies');
        }

        return $builder->useAutowiring(true)->useAnnotations(false)->build();
    }
}
