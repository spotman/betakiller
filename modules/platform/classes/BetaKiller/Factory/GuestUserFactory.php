<?php
declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\Model\GuestUserInterface;

class GuestUserFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * GuestUserFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $builder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilder $builder)
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
