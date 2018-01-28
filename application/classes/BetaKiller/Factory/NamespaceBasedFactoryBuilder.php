<?php
declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\DI\ContainerInterface;

class NamespaceBasedFactoryBuilder
{
    /**
     * @var \BetaKiller\DI\ContainerInterface
     */
    private $container;

    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

    /**
     * @var \MultiSite
     */
    private $multiSite;

    /**
     * NamespaceBasedFactoryBuilder constructor.
     *
     * @param \BetaKiller\DI\ContainerInterface     $container
     * @param \BetaKiller\Config\AppConfigInterface $appConfig
     * @param \MultiSite                            $multiSite
     */
    public function __construct(ContainerInterface $container, AppConfigInterface $appConfig, \MultiSite $multiSite)
    {
        $this->container = $container;
        $this->appConfig = $appConfig;
        $this->multiSite = $multiSite;
    }

    public function createFactory(): NamespaceBasedFactory
    {
        // Always create new instance coz client code is configuring this instance

        /** @var \BetaKiller\Factory\NamespaceBasedFactory $factory */
        $factory = $this->container->make(NamespaceBasedFactory::class);

        if ($this->multiSite->isSiteDetected()) {
            $factory->addRootNamespace($this->appConfig->getNamespace());
        }

        return $factory;
    }
}
