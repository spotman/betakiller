<?php
namespace BetaKiller\Factory;

use BetaKiller\Repository\RepositoryInterface;

class RepositoryFactory implements RepositoryFactoryInterface
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
