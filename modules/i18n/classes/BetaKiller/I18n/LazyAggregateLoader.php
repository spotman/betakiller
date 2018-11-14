<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

use BetaKiller\Helper\AppEnvInterface;
use Psr\Container\ContainerInterface;

class LazyAggregateLoader implements LoaderInterface
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
     * @var \BetaKiller\I18n\LoaderInterface
     */
    private $loader;

    /**
     * LazyAggregateLoader constructor.
     *
     * @param \Psr\Container\ContainerInterface  $container
     * @param \BetaKiller\I18n\I18nConfig        $config
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     */
    public function __construct(ContainerInterface $container, I18nConfig $config, AppEnvInterface $appEnv)
    {
        $this->container = $container;
        $this->appEnv    = $appEnv;

        $this->config = $config;
    }

    /**
     * Returns "key" => "translated string" pairs for provided locale
     *
     * @param string $locale
     *
     * @return string[]
     */
    public function load(string $locale): array
    {
        if (!$this->loader) {
            $this->loader = $this->loaderFactory();
        }

        return $this->loader->load($locale);
    }

    private function loaderFactory(): LoaderInterface
    {
        // Get all loaders from config
        $loadersClassNames = $this->config->getLoaders();

        // Warn if no loaders defined
        if (!$loadersClassNames) {
            throw new I18nException('No i18n loaders defined');
        }

        // If dev mode
        if ($this->appEnv->inDevelopmentMode()) {
            // Inject file-based loader first as a default fallback
            \array_unshift($loadersClassNames, FilesystemLoader::class);
        }

        $loadersInstances = [];

        foreach ($loadersClassNames as $className) {
            $loadersInstances[] = $this->makeLoader($className);
        }

        return new AggregateLoader($loadersInstances);
    }

    private function makeLoader(string $className): LoaderInterface
    {
        return $this->container->get($className);
    }
}
