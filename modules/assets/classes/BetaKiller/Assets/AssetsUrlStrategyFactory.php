<?php
namespace BetaKiller\Assets;

use BetaKiller\Assets\UrlStrategy\AssetsUrlStrategyInterface;
use BetaKiller\Factory\NamespaceBasedFactoryBuilder;
use BetaKiller\Repository\RepositoryInterface;

class AssetsUrlStrategyFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * AssetsUrlStrategyFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     */
    public function __construct(NamespaceBasedFactoryBuilder $factoryBuilder)
    {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->setClassNamespaces('Assets', 'UrlStrategy')
            ->setClassSuffix('AssetsUrlStrategy')
            ->setExpectedInterface(AssetsUrlStrategyInterface::class);
    }

    /**
     * @param string                                     $codename
     * @param \BetaKiller\Repository\RepositoryInterface $repository
     *
     * @return \BetaKiller\Assets\UrlStrategy\AssetsUrlStrategyInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(string $codename, RepositoryInterface $repository): AssetsUrlStrategyInterface
    {
        return $this->factory->create($codename, [
            'repository' => $repository,
        ]);
    }
}
