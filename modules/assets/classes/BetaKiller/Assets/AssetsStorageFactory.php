<?php
namespace BetaKiller\Assets;

use BetaKiller\Assets\Storage\AssetsStorageInterface;
use BetaKiller\Factory\NamespaceBasedFactory;

class AssetsStorageFactory
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
            ->setClassPrefixes('Assets', 'Storage')
            ->setClassSuffix('AssetsStorage')
            ->setExpectedInterface(AssetsStorageInterface::class);
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Assets\Storage\AssetsStorageInterface
     */
    public function create(string $codename): AssetsStorageInterface
    {
        return $this->factory->create($codename);
    }
}
