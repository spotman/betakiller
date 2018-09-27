<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;

class EventFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    public function __construct(NamespaceBasedFactoryBuilder $builder)
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
