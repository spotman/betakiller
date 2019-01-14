<?php
namespace BetaKiller\Auth;

use BetaKiller\Exception\HttpException;

class AccessDeniedException extends HttpException
{
    public function __construct()
    {
        parent::__construct(403);
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
        return 'error.auth.denied';
    }

    /**
     * Показываем пользователю оригинальный текст исключения в красивых обёртках и в JSON-ответе
     */
    public function showOriginalMessageToUser(): bool
    {
        return true;
    }

    /**
     * Overwrite this method with "return TRUE" to show custom message in all cases
     *
     * @return bool
     */
    public function alwaysShowNiceMessage(): bool
    {
        return true;
    }
}
