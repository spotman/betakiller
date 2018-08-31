<?php

use BetaKiller\Task\TaskFactory;

/**
 * Interface that all minion tasks must implement
 */
abstract class Minion_Task extends Kohana_Minion_Task
{
    /**
     * @param string $className
     *
     * @return \BetaKiller\Task\AbstractTask
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    protected static function makeTaskInstance($className): Minion_Task
    {
        if (class_exists($className))
        {
            return parent::makeTaskInstance($className);
        }

        /** @var TaskFactory $factory */
        $factory = \BetaKiller\DI\Container::getInstance()->get(TaskFactory::class);

        return $factory->create($className);
    }
}
