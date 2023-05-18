<?php
declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryInterface;

class TaskFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    /**
     * TaskFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $factoryBuilder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilderInterface $factoryBuilder)
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
