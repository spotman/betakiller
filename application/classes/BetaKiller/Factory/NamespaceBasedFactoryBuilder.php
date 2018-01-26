<?php
declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\DI\ContainerInterface;

class NamespaceBasedFactoryBuilder
{
    /**
     * @var \BetaKiller\DI\ContainerInterface
     */
    private $container;

    /**
     * NamespaceBasedFactoryBuilder constructor.
     *
     * @param \BetaKiller\DI\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function createFactory(): NamespaceBasedFactory
    {
        // Always create new instance coz client code is configuring this instance
        return $this->container->make(NamespaceBasedFactory::class);
    }
}
