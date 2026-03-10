<?php

declare(strict_types=1);

namespace BetaKiller\EventHandler;

use BetaKiller\Event\UserPendingEvent;
use BetaKiller\Workflow\UserWorkflow;

final readonly class UserPendingAutoApprove
{
    public function __construct(private UserWorkflow $workflow)
    {
    }

    public function __invoke(UserPendingEvent $event): void
    {
        $user = $event->getUser();

        // Auto-approve (if enabled)
        if ($user::isAutoApproveEnabled()) {
            $this->workflow->approve($user, $user);
        }
    }
}
