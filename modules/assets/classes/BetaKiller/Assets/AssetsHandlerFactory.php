<?php
namespace BetaKiller\Assets;

use BetaKiller\Assets\Handler\AssetsHandlerInterface;
use BetaKiller\Factory\NamespaceBasedFactoryBuilder;

class AssetsHandlerFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * AssetsStorageFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     */
    public function __construct(NamespaceBasedFactoryBuilder $factoryBuilder)
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
