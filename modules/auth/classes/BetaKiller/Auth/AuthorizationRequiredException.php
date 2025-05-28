<?php
namespace BetaKiller\Auth;

use BetaKiller\Exception\HttpException;

class AuthorizationRequiredException extends HttpException
{
    public function __construct(string $message = null)
    {
        parent::__construct(401, $message);
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

    /**
     * Returns default message for current exception
     * Allows throwing concrete exception without message
     * Useful for custom exception types
     * Return null if no default message allowed
     *
     * @return string
     */
    public function getDefaultMessageI18nKey(): ?string
    {
        return 'error.auth.required';
    }
}
