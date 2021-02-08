<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryInterface;

class DaemonFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    /**
     * DaemonFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $builder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilderInterface $builder)
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
