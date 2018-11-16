<?php
namespace BetaKiller\Notification;

interface MessageRendererInterface
{
    public function makeBody(
        NotificationMessageInterface $message,
        NotificationTargetInterface $target,
        NotificationTransportInterface $transport
    ): string;

    public function makeSubject(
        NotificationMessageInterface $message,
        NotificationTargetInterface $target
    ): string;

    public function hasTemplate(
        string $messageCodename,
        string $transportCodename,
        string $langName
    ): bool;
}
