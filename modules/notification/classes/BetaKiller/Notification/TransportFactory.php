<?php
declare(strict_types=1);

namespace BetaKiller\Notification;

use BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryInterface;

final class TransportFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    /**
     * TransportFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $builder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilderInterface $builder)
    {
        $this->factory = $builder->createFactory()
            ->cacheInstances()
            ->setClassNamespaces('Notification', 'Transport')
            ->setClassSuffix('Transport')
            ->setExpectedInterface(TransportInterface::class);
    }

    public function create(string $codename): TransportInterface
    {
        return $this->factory->create(ucfirst($codename));
    }
}
