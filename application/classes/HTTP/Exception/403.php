<?php defined('SYSPATH') OR die('No direct script access.');

class HTTP_Exception_403 extends Kohana_HTTP_Exception_403 {

    /**
     * Показываем пользователю оригинальный текст исключения в красивых обёртках и в JSON-ответе
     */
    protected function show_original_message_to_user()
    {
        return TRUE;
    }

    /**
     * Отключаем уведомление о текущем типе исключений
     * @return bool
     */
    public function is_notification_enabled()
    {
        return FALSE;
    }

}
