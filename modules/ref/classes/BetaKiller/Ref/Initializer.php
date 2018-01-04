<?php
namespace BetaKiller\Ref;

use BetaKiller\Event\UrlDispatchedEvent;
use BetaKiller\ModuleInitializerInterface;

class Initializer implements ModuleInitializerInterface
{
    /**
     * @Inject
     * @var \BetaKiller\MessageBus\EventBus
     */
    private $eventBus;

    /**
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    public function init(): void
    {
        $this->eventBus->on(UrlDispatchedEvent::class, RefUrlDispatchedEventHandler::class);
    }
}
