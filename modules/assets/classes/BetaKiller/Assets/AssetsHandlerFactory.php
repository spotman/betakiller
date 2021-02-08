<?php
namespace BetaKiller\Assets;

use BetaKiller\Assets\Handler\AssetsHandlerInterface;
use BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryInterface;

class AssetsHandlerFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    /**
     * AssetsStorageFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $factoryBuilder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilderInterface $factoryBuilder)
    {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->setClassNamespaces('Assets', 'Handler')
            ->setClassSuffix('Handler')
            ->setExpectedInterface(AssetsHandlerInterface::class);
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Assets\Handler\AssetsHandlerInterface
     */
    public function create(string $codename): AssetsHandlerInterface
    {
        return $this->factory->create($codename);
    }
}
