<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;

class CommandFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    public function __construct(NamespaceBasedFactoryBuilder $builder)
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
