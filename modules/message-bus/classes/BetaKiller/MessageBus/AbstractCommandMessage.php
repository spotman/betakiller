<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

abstract class AbstractCommandMessage implements CommandMessageInterface
{
    /**
     * Must return true if message requires at least one handler to be processed
     *
     * @return bool
     */
    public function handlersRequired(): bool
    {
        // External command have no local handlers
        return !$this->isExternal();
    }
}
