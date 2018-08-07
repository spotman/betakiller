<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

interface CommandMessageInterface extends MessageInterface
{
    /**
     * Must return true if command requires async processing through queue (immediate result can not be obtained)
     * @return bool
     */
    public function isAsync(): bool;
}
