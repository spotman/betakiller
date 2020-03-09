<?php
declare(strict_types=1);

namespace BetaKiller\Event;

final class UserSuspendedEvent extends AbstractUserWorkflowEvent
{
    /**
     * @inheritDoc
     */
    public function handlersRequired(): bool
    {
        // No handlers yet
        return false;
    }
}
