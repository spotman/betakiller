<?php

declare(strict_types=1);

namespace BetaKiller\EventHandler;

use BetaKiller\Event\UserUnbannedEvent;
use BetaKiller\Workflow\UserWorkflow;

final readonly class UserUnbannedAutoActivate
{
    public function __construct(private UserWorkflow $workflow)
    {
    }

    public function __invoke(UserUnbannedEvent $event): void
    {
        $user = $event->getUser();

        // Auto-activate (if enabled)
        if ($user::isAutoActivationEnabled()) {
            $this->workflow->activate($user, $user);
        }
    }
}
