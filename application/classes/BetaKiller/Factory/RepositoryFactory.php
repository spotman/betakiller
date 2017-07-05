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
     * @param \BetaKiller\Factory\NamespaceBasedFactory $factory
     */
    public function __construct(NamespaceBasedFactory $factory)
    {
        $this->factory = $factory;

        $this->injectDefinitions($this->factory);
    }

    public function injectDefinitions(NamespaceBasedFactory $factory): void
    {
        $factory
            ->cacheInstances()
            ->setExpectedInterface(RepositoryInterface::class)
            ->setClassPrefixes(RepositoryInterface::CLASS_PREFIX)
            ->setClassSuffix(RepositoryInterface::CLASS_SUFFIX);
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Repository\RepositoryInterface|mixed
     */
    public function create(string $codename): RepositoryInterface
    {
        return $this->factory->create($codename);
    }
}
