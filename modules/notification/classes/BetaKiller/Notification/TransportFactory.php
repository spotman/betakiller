<?php
declare(strict_types=1);

namespace BetaKiller\Notification;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;

final class TransportFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * TransportFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $builder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilder $builder)
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
