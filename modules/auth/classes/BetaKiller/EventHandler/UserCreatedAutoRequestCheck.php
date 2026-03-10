<?php

declare(strict_types=1);

namespace BetaKiller\EventHandler;

use BetaKiller\Event\UserCreatedEvent;
use BetaKiller\Workflow\UserWorkflow;

final readonly class UserCreatedAutoRequestCheck
{
    public function __construct(private UserWorkflow $workflow)
    {
    }

    public function __invoke(UserCreatedEvent $event): void
    {
        $user = $event->getUser();

        // Auto-approve (if enabled)
        if ($user::isAutoApproveEnabled()) {
            $this->workflow->requestCheck($user);
        }
    }
}
