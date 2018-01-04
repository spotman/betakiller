<?php
namespace BetaKiller\Assets;

use BetaKiller\Assets\Handler\AssetsHandlerInterface;
use BetaKiller\Factory\NamespaceBasedFactory;

class AssetsHandlerFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * AssetsStorageFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactory $factory
     */
    public function __construct(NamespaceBasedFactory $factory)
    {
        $this->factory = $factory
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
