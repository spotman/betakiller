<?php

declare(strict_types=1);

namespace BetaKiller\Task\Test\Event;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\MessageBus\EventMessageInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Task\AbstractTask;

abstract class AbstractUserEventTask extends AbstractTask
{
    /**
     * @var \BetaKiller\MessageBus\EventBusInterface
     */
    private EventBusInterface $eventBus;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private UserInterface $user;

    public function __construct(EventBusInterface $eventBus, UserInterface $user)
    {
        $this->eventBus = $eventBus;
        $this->user     = $user;
    }

    /**
     * @inheritDoc
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [
            // No options
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $event = $this->makeEvent($this->user);

        $this->eventBus->emit($event);
    }

    abstract protected function makeEvent(UserInterface $user): EventMessageInterface;
}
