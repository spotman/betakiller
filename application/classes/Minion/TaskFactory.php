<?php
declare(strict_types=1);

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;
use BetaKiller\Task\AbstractTask;

class Minion_TaskFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * Minion_TaskFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilder $factoryBuilder)
    {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->setExpectedInterface(AbstractTask::class);
    }

    /**
     * @param string $className
     *
     * @return \BetaKiller\Task\AbstractTask
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(string $className): AbstractTask
    {
        return $this->factory->create($className);
    }
}
