<?php
namespace BetaKiller\Assets;

use BetaKiller\Assets\UrlStrategy\AssetsUrlStrategyInterface;
use BetaKiller\Factory\NamespaceBasedFactory;

class AssetsUrlStrategyFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * AssetsUrlStrategyFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactory $factory
     */
    public function __construct(NamespaceBasedFactory $factory)
    {
        $this->factory = $factory
            ->setClassPrefixes('Assets', 'UrlStrategy')
            ->setClassSuffix('AssetsUrlStrategy')
            ->setExpectedInterface(AssetsUrlStrategyInterface::class);
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Assets\UrlStrategy\AssetsUrlStrategyInterface
     */
    public function create(string $codename): AssetsUrlStrategyInterface
    {
        return $this->factory->create($codename);
    }
}
