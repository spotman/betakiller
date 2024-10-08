<?php
declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\Url\MenuCounterInterface;

final class MenuCounterFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    /**
     * MenuCounterFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $builder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilderInterface $builder)
    {
        $this->factory = $builder->createFactory()
            ->setClassNamespaces(...MenuCounterInterface::NAMESPACES)
            ->setClassSuffix(MenuCounterInterface::SUFFIX)
            ->setExpectedInterface(MenuCounterInterface::class)
            ->cacheInstances();
    }

    public function create(string $codename): MenuCounterInterface
    {
        return $this->factory->create($codename);
    }
}
