<?php
namespace BetaKiller\Factory;

use BetaKiller\Repository\RepositoryInterface;

class RepositoryFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * RepositoryFactory constructor.
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
            ->cacheInstances()
            ->setExpectedInterface(RepositoryInterface::class)
            ->setClassNamespaces(RepositoryInterface::CLASS_PREFIX)
            ->setClassSuffix(RepositoryInterface::CLASS_SUFFIX);
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Repository\RepositoryInterface|mixed
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(string $codename): RepositoryInterface
    {
        return $this->factory->create($codename);
    }
}
