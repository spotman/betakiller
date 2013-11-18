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

    protected $_use_redirect_url = TRUE;

    /**
     * Generate a Response for the 401 Exception.
     *
     * The user should be redirect to a login page.
     *
     * @return Response
     */
    public function get_response()
    {
        // Relocating user to login page (if this is not AJAX request)
        if ( ! Request::current()->is_ajax() )
        {
            $this->_response->headers('Location', $this->get_login_page_url());
        }

        return $this->_response;
    }

    protected function get_login_page_url()
    {
        $url = Route::url('login');

        $redirect_url = Request::current()->detect_uri();

        if ( $redirect_url AND $this->_use_redirect_url )
        {
            $url .= '?return='. $redirect_url;
        }

        return $url;
    }

    /**
     * Remove redirect url from login form
     */
    public function remove_redirect_url()
    {
        $this->_use_redirect_url = FALSE;
        return $this;
    }
}