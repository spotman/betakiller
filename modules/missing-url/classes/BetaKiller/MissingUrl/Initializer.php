<?php
declare(strict_types=1);

namespace BetaKiller\MissingUrl;

use BetaKiller\Event\MissingUrlEvent;
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
        $this->eventBus->on(MissingUrlEvent::class, MissingUrlEventHandler::class);
    }
}
