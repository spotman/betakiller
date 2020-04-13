<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

use BetaKiller\Helper\AppEnvInterface;
use Psr\Container\ContainerInterface;

class LazyAggregateI18nLoader implements I18nKeysLoaderInterface
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var I18nConfig
     */
    private $config;

    /**
     * @var \BetaKiller\I18n\I18nKeysLoaderInterface
     */
    private $loader;

    /**
     * LazyAggregateI18nLoader constructor.
     *
     * @param \Psr\Container\ContainerInterface  $container
     * @param \BetaKiller\I18n\I18nConfig        $config
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     */
    public function __construct(ContainerInterface $container, I18nConfig $config, AppEnvInterface $appEnv)
    {
        $this->container = $container;
        $this->config    = $config;
        $this->appEnv    = $appEnv;
    }

    /**
     * Returns "key" => "translated string" pairs for provided locale
     *
     * @return string[]
     * @throws \BetaKiller\I18n\I18nException
     */
    public function loadI18nKeys(): array
    {
        if (!$this->loader) {
            $this->loader = $this->loaderFactory();
        }

        return $this->loader->loadI18nKeys();
    }

    private function loaderFactory(): I18nKeysLoaderInterface
    {
        // Get all loaders from config
        $loadersClassNames = $this->config->getLoaders();

        // Warn if no loaders defined
        if (!$loadersClassNames) {
            throw new I18nException('No i18n loaders defined');
        }

//        // If dev mode
//        if ($this->appEnv->inDevelopmentMode()) {
//            // Inject file-based loader as a primary source fallback
//            $loadersClassNames[] = FilesystemI18nKeysLoader::class;
//        }

        $loadersInstances = [];

        foreach ($loadersClassNames as $className) {
            $loadersInstances[] = $this->makeLoader($className);
        }

        return new AggregateLoader($loadersInstances);
    }

    private function makeLoader(string $className): I18nKeysLoaderInterface
    {
        return $this->container->get($className);
    }
}
