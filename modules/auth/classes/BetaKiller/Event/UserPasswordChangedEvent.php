<?php
declare(strict_types=1);

namespace BetaKiller\Event;

final class UserPasswordChangedEvent extends AbstractUserWorkflowEvent
{
    /**
     * @inheritDoc
     */
    public function handlersRequired(): bool
    {
        return true;
    }
}
