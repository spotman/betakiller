<?php defined('SYSPATH') OR die('No direct script access.');

class HTTP_Exception_401 extends Kohana_HTTP_Exception_401 {

    /**
     * Показываем пользователю оригинальный текст исключения в красивых обёртках и в JSON-ответе
     */
    protected function showOriginalMessageToUser()
    {
        return TRUE;
    }

    /**
     * Отключаем уведомление о текущем типе исключений
     * @return bool
     */
    public function isNotificationEnabled()
    {
        return FALSE;
    }

    protected function alwaysShowNiceMessage()
    {
        return TRUE;
    }

    /**
     * @param $code
     * @return IFace_Auth_Login
     */
    protected function getIFaceFromCode($code)
    {
        return $this->createIFaceFromCodename('Auth_Login');
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
