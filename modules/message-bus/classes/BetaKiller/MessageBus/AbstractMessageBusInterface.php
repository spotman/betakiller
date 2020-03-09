<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

interface AbstractMessageBusInterface
{
    /**
     * @param string   $messageClassName
     * @param callable $handler
     */
    public function on(string $messageClassName, callable $handler): void;
}
