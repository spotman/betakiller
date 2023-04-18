<?php
declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\DI\ContainerInterface;
use BetaKiller\Env\AppEnvInterface;

class NamespaceBasedFactoryBuilder implements NamespaceBasedFactoryBuilderInterface
{
    /**
     * @var \BetaKiller\DI\ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private AppConfigInterface $appConfig;

    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * NamespaceBasedFactoryBuilder constructor.
     *
     * @param \BetaKiller\DI\ContainerInterface     $container
     * @param \BetaKiller\Config\AppConfigInterface $appConfig
     * @param \BetaKiller\Env\AppEnvInterface       $appEnv
     */
    public function __construct(ContainerInterface $container, AppConfigInterface $appConfig, AppEnvInterface $appEnv)
    {
        $this->container = $container;
        $this->appConfig = $appConfig;
        $this->appEnv    = $appEnv;
    }

    /**
     * @return \BetaKiller\Factory\NamespaceBasedFactoryInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createFactory(): NamespaceBasedFactoryInterface
    {
        // Always create new instance coz client code is configuring this instance
        try {
            /** @var \BetaKiller\Factory\NamespaceBasedFactory $factory */
            $factory = $this->container->make(NamespaceBasedFactory::class);

            // Add project namespace as primary one
            if ($this->appEnv->isAppRunning()) {
                $factory->addRootNamespace($this->appConfig->getNamespace());
            }

            // Add core namespace as fallback
            $factory->addRootNamespace('BetaKiller');

            return $factory;
        } catch (\Throwable $e) {
            throw FactoryException::wrap($e);
        }
    }
}
