<?php
namespace BetaKiller\Factory;

use BetaKiller\Model\ExtendedOrmInterface;

class OrmFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * OrmFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactory $factory
     */
    public function __construct(NamespaceBasedFactory $factory)
    {
        $this->factory = $factory;

        $this->injectDefinitions($this->factory);
    }

    public function injectDefinitions(NamespaceBasedFactory $factory): void
    {
        $factory
            ->setExpectedInterface(ExtendedOrmInterface::class)
            ->setClassNamespaces('Model')
            ->rawInstances();
    }

    public function create(string $name): ExtendedOrmInterface
    {
        return $this->factory->create($name);
    }
}
