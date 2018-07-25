<?php
declare(strict_types=1);

namespace BetaKiller\Url\Zone;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;

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

    public function create(string $zoneName): ZoneAccessSpecInterface
    {
        return $this->factory->create(\ucfirst($zoneName));
    }
}
