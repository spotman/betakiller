<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

use BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryInterface;

class EventFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    public function __construct(NamespaceBasedFactoryBuilderInterface $builder)
    {
        $this->factory = $builder->createFactory()
            ->setClassNamespaces('Event')
            ->setClassSuffix(EventMessageInterface::SUFFIX)
            ->setExpectedInterface(EventMessageInterface::class);
    }

    /**
     * @param string     $name
     *
     * @param array|null $arguments
     *
     * @return \BetaKiller\MessageBus\EventMessageInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(string $name, array $arguments = null): EventMessageInterface
    {
        return $this->factory->create($name, $arguments);
    }
}
