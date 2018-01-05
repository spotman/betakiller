<?php
namespace BetaKiller\Ref;

use BetaKiller\Event\UrlDispatchedEvent;
use BetaKiller\MessageBus\EventBus;
use BetaKiller\ModuleInitializerInterface;

class Initializer implements ModuleInitializerInterface
{
    /**
     * @var \BetaKiller\MessageBus\EventBus
     */
    private $eventBus;

    /**
     * Initializer constructor.
     *
     * @param \BetaKiller\MessageBus\EventBus $eventBus
     */
    public function __construct(EventBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    public function init(): void
    {
        $this->eventBus->on(UrlDispatchedEvent::class, RefUrlDispatchedEventHandler::class);
    }
}
