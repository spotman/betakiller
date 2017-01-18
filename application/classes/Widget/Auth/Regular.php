<?php defined('SYSPATH') OR die('No direct script access.');

use \BetaKiller\IFace\Widget;

class Widget_Auth_Regular extends Widget
{
    /**
     * @var Auth
     * @Inject
     */
    private $auth;

    /**
     * Widget_Auth_Regular constructor.
     *
     * @param \Auth $auth
     */
    public function __construct(\Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Action for logging in
     */
    public function action_login()
    {
        if ( ! $this->getRequest()->is_ajax() ) {
            throw new HTTP_Exception_400('AJAX only gateway');
        }

        // Magic call for better exception handling
        $this->content_type_json();

        $user_login     = $this->getRequest()->post("user-login");
        $user_password  = $this->getRequest()->post("user-password");
        $remember       = (bool) $this->getRequest()->post("remember");

        // Sanitize
        $user_login     = trim(HTML::chars($user_login));
        $user_password  = trim(HTML::chars($user_password));

        if (!$user_login || !$user_password) {
            throw new HTTP_Exception_400('No username or password sent');
        }

        $this->auth->login($user_login, $user_password, $remember);

        // Возвращаем соответствующий ответ
        $this->send_json(Response::JSON_SUCCESS);
    }

    public function get_data()
    {
        return array(
            'login_url'             =>  $this->get_login_url(),
            'reset_password_url'    =>  $this->get_reset_password_url(),
        );
    }

    protected function get_login_url()
    {
        return $this->url('login');
    }

    protected function get_reset_password_url()
    {
        /** @var IFace_Auth_Password_Reset $iface */
        $iface = $this->iface_from_codename('Auth_Password_Reset');
        return $iface->url();
    }
}
