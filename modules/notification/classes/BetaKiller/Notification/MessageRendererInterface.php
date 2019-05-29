<?php
namespace BetaKiller\Notification;

interface MessageRendererInterface
{
    public function makeBody(
        MessageInterface $message,
        TargetInterface $target,
        TransportInterface $transport,
        string $hash
    ): string;

    public function makeSubject(
        MessageInterface $message,
        TargetInterface $target
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
