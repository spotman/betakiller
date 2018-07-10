<?php
namespace BetaKiller\DI\Container;

use BetaKiller\Config\KohanaConfigProvider;
use DI\ContainerBuilder;

class KohanaBasedContainer extends AbstractContainer
{
    /**
     * @return \BetaKiller\DI\ContainerInterface|\DI\Container|mixed
     */
    protected function containerFactory()
    {
        $builder = new ContainerBuilder();

        $configProvider = new KohanaConfigProvider();

        $config = $configProvider->load(['php-di']);

        $definitions    = $config->get('definitions');
        $useAutowiring  = $config->get('autowiring', true);
        $useAnnotations = $config->get('annotations', true);

        /** @url http://php-di.org/doc/performances.html */
        $compileTo        = $config->get('compile_to');
        $cacheDefinitions = $config->get('cache_definitions');

        if ($compileTo) {
            $builder->enableCompilation($compileTo);
        }

        if ($cacheDefinitions) {
            $builder->enableDefinitionCache();
        }

        $builder->addDefinitions($definitions);

        return $builder
            ->useAutowiring($useAutowiring)
            ->useAnnotations($useAnnotations)
            ->build();
    }
}
