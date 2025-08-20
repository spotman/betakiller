<?php
namespace BetaKiller\Notification;

use BetaKiller\Notification\Message\MessageInterface;

interface MessageRendererInterface
{
    public function makeBody(
        MessageInterface $message,
        MessageTargetInterface $target,
        TransportInterface $transport
    ): string;

    public function makeSubject(
        MessageInterface $message,
        MessageTargetInterface $target
    ): string;

    public function hasLocalizedTemplate(
        string $messageCodename,
        string $langName
    ): bool;

    public function hasGeneralTemplate(
        string $messageCodename
    ): bool;
}
