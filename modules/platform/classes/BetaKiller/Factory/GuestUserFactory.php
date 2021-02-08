<?php
declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\Model\GuestUserInterface;

class GuestUserFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    /**
     * GuestUserFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $builder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilderInterface $builder)
    {
        $this->factory = $builder->createFactory()
            ->cacheInstances()
            ->rawInstances()
            ->setClassNamespaces('Model')
            ->setExpectedInterface(GuestUserInterface::class);
    }

    public function create(): GuestUserInterface
    {
        return $this->factory->create('GuestUser');
    }
}
