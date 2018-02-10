<?php
declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\DI\ContainerInterface;
use BetaKiller\Helper\AppEnvInterface;

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
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * NamespaceBasedFactoryBuilder constructor.
     *
     * @param \BetaKiller\DI\ContainerInterface     $container
     * @param \BetaKiller\Config\AppConfigInterface $appConfig
     * @param \BetaKiller\Helper\AppEnvInterface    $appEnv
     */
    public function __construct(ContainerInterface $container, AppConfigInterface $appConfig, AppEnvInterface $appEnv)
    {
        $this->container = $container;
        $this->appConfig = $appConfig;
        $this->appEnv    = $appEnv;
    }

    public function createFactory(): NamespaceBasedFactory
    {
        // Always create new instance coz client code is configuring this instance

        try {
            /** @var \BetaKiller\Factory\NamespaceBasedFactory $factory */
            $factory = $this->container->make(NamespaceBasedFactory::class);

            if (!$this->appEnv->isCoreRunning()) {
                $factory->addRootNamespace($this->appConfig->getNamespace());
            }

            return $factory;
        } catch (\Throwable $e) {
            throw FactoryException::wrap($e);
        }
    }
}
