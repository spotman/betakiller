<?php
declare(strict_types=1);

namespace BetaKiller\Url\Zone;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;
use BetaKiller\Url\UrlElementWithZoneInterface;

class ZoneAccessSpecFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * ZoneAccessSpecFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $builder
     */
    public function __construct(NamespaceBasedFactoryBuilder $builder)
    {
        $this->factory = $builder
            ->createFactory()
            ->setClassNamespaces(...ZoneAccessSpecInterface::NAMESPACES)
            ->setClassSuffix(ZoneAccessSpecInterface::SUFFIX)
            ->setExpectedInterface(ZoneAccessSpecInterface::class)
            ->cacheInstances();
    }

    public function createFromUrlElement(UrlElementWithZoneInterface $urlElement): ZoneAccessSpecInterface
    {
        return $this->create($urlElement->getZoneName());
    }

    public function create(string $zoneName): ZoneAccessSpecInterface
    {
        return $this->factory->create(\ucfirst($zoneName));
    }
}
