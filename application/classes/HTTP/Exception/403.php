<?php defined('SYSPATH') OR die('No direct script access.');

class HTTP_Exception_403 extends Kohana_HTTP_Exception_403
{
    /**
     * Показываем пользователю оригинальный текст исключения в красивых обёртках и в JSON-ответе
     */
    protected function showOriginalMessageToUser()
    {
        return true;
    }

    /**
     * Overwrite this method with "return TRUE" to show custom message in all cases
     *
     * @return bool
     */
    protected function alwaysShowNiceMessage(): bool
    {
        return true;
    }

    /**
     * Отключаем уведомление о текущем типе исключений
     *
     * @return bool
     */
    public function isNotificationEnabled()
    {
        return false;
    }
}
