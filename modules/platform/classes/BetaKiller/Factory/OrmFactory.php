<?php
namespace BetaKiller\Factory;

use BetaKiller\Model\ExtendedOrmInterface;

class OrmFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    /**
     * OrmFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $factoryBuilder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilderInterface $factoryBuilder)
    {
        $this->factory = $factoryBuilder->createFactory();

        $this->injectDefinitions($this->factory);
    }

    public function injectDefinitions(NamespaceBasedFactoryInterface $factory): void
    {
        $factory
            ->setExpectedInterface(ExtendedOrmInterface::class)
            ->setClassNamespaces('Model')
            ->rawInstances();
    }

    /**
     * @param string $name
     *
     * @return \BetaKiller\Model\ExtendedOrmInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(string $name): ExtendedOrmInterface
    {
        return $this->factory->create($name);
    }
}
