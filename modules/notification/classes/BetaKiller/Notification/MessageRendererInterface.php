<?php
namespace BetaKiller\Notification;


interface MessageRendererInterface
{
    public function render(
        NotificationMessageInterface $message,
        NotificationUserInterface $target,
        NotificationTransportInterface $transport
    ): string;
}
