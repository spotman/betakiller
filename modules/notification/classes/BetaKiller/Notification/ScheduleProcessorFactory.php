<?php
declare(strict_types=1);

namespace BetaKiller\Notification;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;
use BetaKiller\Notification\ScheduleProcessor\ScheduleProcessorInterface;

final class ScheduleProcessorFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * ScheduleProcessorFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $builder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilder $builder)
    {
        $this->factory = $builder->createFactory()
            ->cacheInstances()
            ->setClassNamespaces('Notification', 'ScheduleProcessor')
            ->setClassSuffix('Processor')
        ->setExpectedInterface(ScheduleProcessorInterface::class);
    }

    public function create(string $messageCodename): ScheduleProcessorInterface
    {
        // Convert message codename to CamelCase without symbols
        $codename = \preg_replace('/[^A-Za-z]+/', '_', $messageCodename);
        $codename = \implode('', \array_map('ucfirst', \explode('_', $codename)));

        return $this->factory->create($codename);
    }
}
