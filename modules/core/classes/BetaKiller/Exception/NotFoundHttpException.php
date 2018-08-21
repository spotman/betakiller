<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

class NotFoundHttpException extends \HTTP_Exception_404
{
    /**
     * Отключаем уведомление о текущем типе исключений
     * @return bool
     */
    public function isNotificationEnabled(): bool
    {
        return FALSE;
    }

    public function alwaysShowNiceMessage(): bool
    {
        return TRUE;
    }
}
