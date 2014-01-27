<?php defined('SYSPATH') OR die('No direct script access.');

class HTTP_Exception_401 extends Kohana_HTTP_Exception_401 {

    /**
     * Показываем пользователю оригинальный текст исключения в красивых обёртках и в JSON-ответе
     */
    protected $_show_original_message_to_user = TRUE;

    /**
     * Отключаем уведомление о текущем типе исключений
     * @var bool
     */
    protected $_send_notification = FALSE;

    /**
     * Generate a Response for the 401 Exception.
     * The user should see the login page.
     *
     * @return Response
     */
    public function get_response()
    {
        /** @var IFace_Auth_Login $login_iface */
        $login_iface = IFace::by_codename('Auth_Login');

        // Show login page
        $this->_response->send_string($login_iface);

        return $this->_response;
    }

}