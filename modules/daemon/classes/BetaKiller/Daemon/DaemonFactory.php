<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;

class DaemonFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * DaemonFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $builder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilder $builder)
    {
        $this->factory = $builder->createFactory()
            ->setClassNamespaces(...DaemonInterface::NAMESPACES)
            ->setClassSuffix(DaemonInterface::SUFFIX)
            ->setExpectedInterface(DaemonInterface::class);
    }

    public function create(string $codename): DaemonInterface
    {
        return $this->factory->create($codename);
    }
}
