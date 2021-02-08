<?php
declare(strict_types=1);

namespace BetaKiller\Url\Zone;

use BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryInterface;
use BetaKiller\Url\UrlElementInterface;

class ZoneAccessSpecFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    /**
     * ZoneAccessSpecFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $builder
     */
    public function __construct(NamespaceBasedFactoryBuilderInterface $builder)
    {
        $this->factory = $builder
            ->createFactory()
            ->setClassNamespaces(...ZoneAccessSpecInterface::NAMESPACES)
            ->setClassSuffix(ZoneAccessSpecInterface::SUFFIX)
            ->setExpectedInterface(ZoneAccessSpecInterface::class)
            ->cacheInstances();
    }

    public function createFromUrlElement(UrlElementInterface $urlElement): ZoneAccessSpecInterface
    {
        return $this->create($urlElement->getZoneName());
    }

    public function create(string $zoneName): ZoneAccessSpecInterface
    {
        $names    = explode('-', $zoneName);
        $names    = \array_map('ucfirst', $names);
        $codename = implode('', $names);

        return $this->factory->create($codename);
    }
}
