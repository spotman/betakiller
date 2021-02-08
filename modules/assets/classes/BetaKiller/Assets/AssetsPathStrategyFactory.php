<?php
namespace BetaKiller\Assets;

use BetaKiller\Assets\PathStrategy\AssetsPathStrategyInterface;
use BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryInterface;
use BetaKiller\Repository\RepositoryInterface;

class AssetsPathStrategyFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    /**
     * AssetsPathStrategyFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $factoryBuilder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilderInterface $factoryBuilder)
    {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->setClassNamespaces('Assets', 'PathStrategy')
            ->setClassSuffix('AssetsPathStrategy')
            ->setExpectedInterface(AssetsPathStrategyInterface::class);
    }

    /**
     * @param string                                     $codename
     * @param \BetaKiller\Repository\RepositoryInterface $repository
     *
     * @return \BetaKiller\Assets\PathStrategy\AssetsPathStrategyInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(string $codename, RepositoryInterface $repository): AssetsPathStrategyInterface
    {
        return $this->factory->create($codename, [
            'repository' => $repository,
        ]);
    }
}
