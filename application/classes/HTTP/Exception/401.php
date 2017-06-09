<?php defined('SYSPATH') OR die('No direct script access.');

class HTTP_Exception_401 extends Kohana_HTTP_Exception_401
{
    /**
     * Показываем пользователю оригинальный текст исключения в красивых обёртках и в JSON-ответе
     */
    protected function showOriginalMessageToUser()
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

    protected function alwaysShowNiceMessage()
    {
        return true;
    }
}
