<?php
namespace BetaKiller\Notification;

interface MessageRendererInterface
{
    public function render(
        NotificationMessageInterface $message,
        NotificationUserInterface $target,
        NotificationTransportInterface $transport,
        array $additionalData = null
    ): string;

    public function makeSubject(NotificationMessageInterface $message, NotificationUserInterface $target): string;
}
