<?php
declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;
use BetaKiller\Helper\UserDetector;

class TaskFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * @var \BetaKiller\Helper\UserDetector
     */
    private $userDetector;

    /**
     * TaskFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     * @param \BetaKiller\Helper\UserDetector                  $userDetector
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilder $factoryBuilder, UserDetector $userDetector)
    {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->setExpectedInterface(AbstractTask::class);

        $this->userDetector = $userDetector;
    }

    /**
     * @param string $className
     *
     * @return \BetaKiller\Task\AbstractTask
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(string $className): AbstractTask
    {
        return $this->factory->create($className, [
            'user' => $this->userDetector->detectCliUser(),
        ]);
    }
}
