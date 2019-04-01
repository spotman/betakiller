<?php
namespace BetaKiller\Notification;

interface MessageRendererInterface
{
    public function makeBody(
        NotificationMessageInterface $message,
        NotificationTargetInterface $target,
        NotificationTransportInterface $transport,
        string $hash
    ): string;

    public function makeSubject(
        NotificationMessageInterface $message,
        NotificationTargetInterface $target
    ): string;

    public function hasLocalizedTemplate(
        string $messageCodename,
        string $transportCodename,
        string $langName
    ): bool;

    public function hasGeneralTemplate(
        string $messageCodename,
        string $transportCodename
    ): bool;
}
