<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

class NotFoundHttpException extends HttpException
{
    public function __construct(string $message = null, array $values = null)
    {
        parent::__construct(404, $message, $values);
    }

    /**
     * Отключаем уведомление о текущем типе исключений
     *
     * @return bool
     */
    public function isNotificationEnabled(): bool
    {
        return false;
    }

    public function alwaysShowNiceMessage(): bool
    {
        return true;
    }
}
