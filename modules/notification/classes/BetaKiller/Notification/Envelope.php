<?php

declare(strict_types=1);

namespace BetaKiller\Notification;

use BetaKiller\Notification\Message\MessageInterface;

final readonly class Envelope implements EnvelopeInterface
{
    public function __construct(private MessageTargetInterface $target, private MessageInterface $message)
    {
    }

    public function getMessage(): MessageInterface
    {
        return $this->message;
    }

    public function getTarget(): MessageTargetInterface
    {
        return $this->target;
    }
}
