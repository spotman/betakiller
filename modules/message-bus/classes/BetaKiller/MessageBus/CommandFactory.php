<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

use BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryInterface;

class CommandFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    public function __construct(NamespaceBasedFactoryBuilderInterface $builder)
    {
        $this->factory = $builder->createFactory()
            ->setClassNamespaces('Command')
            ->setClassSuffix(CommandMessageInterface::SUFFIX)
            ->setExpectedInterface(CommandMessageInterface::class);
    }

    /**
     * @param string     $name
     *
     * @param array|null $arguments
     *
     * @return \BetaKiller\MessageBus\CommandMessageInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(string $name, array $arguments = null): CommandMessageInterface
    {
        return $this->factory->create($name, $arguments);
    }
}
