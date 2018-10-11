<?php
namespace BetaKiller\Auth;

use BetaKiller\Exception\HttpException;

class AuthorizationRequiredException extends HttpException
{
    public function __construct()
    {
        parent::__construct(401);
    }

    /**
     * Показываем пользователю оригинальный текст исключения в красивых обёртках и в JSON-ответе
     */
    public function showOriginalMessageToUser(): bool
    {
        return true;
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
