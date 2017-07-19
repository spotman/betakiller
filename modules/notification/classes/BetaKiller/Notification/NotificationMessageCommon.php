<?php
namespace BetaKiller\Notification;

use Twig;

class NotificationMessageCommon extends NotificationMessageAbstract
{
    protected function template_factory(): \View
    {
        return Twig::factory();
    }
}
