<?php
namespace BetaKiller\Notification;

use Twig;

class NotificationMessageCommon extends NotificationMessageAbstract
{
    protected function template_factory()
    {
        return Twig::factory();
    }
}
