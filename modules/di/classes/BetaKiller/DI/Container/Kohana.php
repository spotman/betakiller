<?php
namespace BetaKiller\DI\Container;

use DI\ContainerBuilder;
use Doctrine\Common\Cache;

class Kohana extends Base
{
    protected function containerFactory()
    {
        $builder = new ContainerBuilder();

        $definitions = \Kohana::find_file('config', 'php-di', null, true);

        foreach (array_reverse($definitions) as $defFile) {
            $builder->addDefinitions($defFile);
        }

        // Check environment
        if ( \Kohana::in_production(TRUE) ) {
            // TODO deal with caching

//            /** @url http://php-di.org/doc/performances.html */
//            $cache = new Cache\ApcCache();
//            $cache->setNamespace('php-di');
//
//            $builder->setDefinitionCache($cache);
//            $builder->writeProxiesToFile(true, 'tmp/proxies');
        }

        return $builder->useAutowiring(true)->useAnnotations(false)->build();
    }
}
