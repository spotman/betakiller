<?php
declare(strict_types=1);

namespace BetaKiller\Event;

final class AccessRecoveryRequestedEvent extends AbstractWebAuthRequestedEvent
{
    /**
     * Must return true if message requires at least one handler to be processed
     *
     * @return bool
     */
    public function handlersRequired(): bool
    {
        return false;
    }
}
