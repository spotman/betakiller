<?php
declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;
use Minion_Task;

class TaskFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * TaskFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilder $factoryBuilder)
    {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->setExpectedInterface(Minion_Task::class); // Migrations use Minion_Task class
    }

    /**
     * @param string $className
     *
     * @return \Minion_Task
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(string $className): Minion_Task
    {
        return $this->factory->create($className);
    }
}
