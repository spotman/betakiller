<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

class NotFoundHttpException extends HttpException
{
    public function __construct()
    {
        parent::__construct(404);
    }

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
