<?php
namespace BetaKiller\Factory;

use BetaKiller\Model\ExtendedOrmInterface;

class OrmFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * OrmFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     */
    public function __construct(NamespaceBasedFactoryBuilder $factoryBuilder)
    {
        $this->factory = $factoryBuilder->createFactory();

        $this->injectDefinitions($this->factory);
    }

    public function injectDefinitions(NamespaceBasedFactory $factory): void
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
