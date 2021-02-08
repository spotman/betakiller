<?php
namespace BetaKiller\Factory;

use BetaKiller\Repository\RepositoryInterface;

class RepositoryFactory implements RepositoryFactoryInterface
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    /**
     * RepositoryFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $factoryBuilder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilderInterface $factoryBuilder)
    {
        $this->factory = $factoryBuilder->createFactory();

        self::injectDefinitions($this->factory);
    }

    public static function injectDefinitions(NamespaceBasedFactoryInterface $factory): void
    {
        $factory
            ->cacheInstances()
            ->setExpectedInterface(RepositoryInterface::class)
            ->setClassNamespaces(RepositoryInterface::CLASS_PREFIX)
            ->setClassSuffix(RepositoryInterface::CLASS_SUFFIX)
            ->useInterface();
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Repository\RepositoryInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(string $codename): RepositoryInterface
    {
        return $this->factory->create($codename);
    }
}
