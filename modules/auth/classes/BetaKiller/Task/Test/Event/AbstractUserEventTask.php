<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test\Event;

use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\MessageBus\EventMessageInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Task\AbstractTask;

abstract class AbstractUserEventTask extends AbstractTask
{
    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private UserInterface $user;

    /**
     * @var \BetaKiller\MessageBus\EventBusInterface
     */
    private EventBusInterface $eventBus;

    public function __construct(UserInterface $user, EventBusInterface $eventBus)
    {
        parent::__construct();

        $this->user = $user;
        $this->eventBus = $eventBus;
    }

    /**
     * @inheritDoc
     */
    public function defineOptions(): array
    {
        return [
            // No options
        ];
    }

    public function run(): void
    {
        $event = $this->makeEvent($this->user);

        $this->eventBus->emit($event);
    }

    abstract protected function makeEvent(UserInterface $user): EventMessageInterface;
}
