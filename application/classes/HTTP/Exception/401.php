<?php defined('SYSPATH') OR die('No direct script access.');

class HTTP_Exception_401 extends Kohana_HTTP_Exception_401 {

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

    protected function always_show_nice_message()
    {
        return TRUE;
    }

    /**
     * @param $code
     * @return IFace_Auth_Login
     */
    protected function get_iface($code)
    {
        return $this->iface_from_codename('Auth_Login');
    }

    /**
     * Force custom message
     *
     * @return Response
     */
    public function get_response()
    {
        return Kohana_Exception::_handler($this);
    }

}
