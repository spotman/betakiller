<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

interface AbstractMessageBusInterface
{
    /**
     * @param string $messageClassName
     * @param string $handlerClassName
     */
    public function on(string $messageClassName, string $handlerClassName): void;
}
