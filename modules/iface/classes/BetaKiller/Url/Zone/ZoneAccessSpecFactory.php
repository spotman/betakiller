<?php
declare(strict_types=1);

namespace BetaKiller\Url\Zone;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;
use BetaKiller\Url\UrlElementInterface;

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
