<?php
declare(strict_types=1);

namespace BetaKiller\Event;

final class UserEmailChangedEvent extends AbstractUserWorkflowEvent
{
    /**
     * @inheritDoc
     */
    public function handlersRequired(): bool
    {
        return true;
    }
}
