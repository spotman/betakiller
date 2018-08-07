<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

interface AbstractMessageBusInterface
{
    /**
     * @param string       $messageClassName
     * @param string|mixed $handler
     *
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    public function on(string $messageClassName, $handler): void;
}
