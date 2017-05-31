<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\Helper\IFaceHelper;
use BetaKiller\IFace\Widget\AbstractBaseWidget;

class Widget_Auth_Regular extends AbstractBaseWidget
{
    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * Widget_Auth_Regular constructor.
     *
     * @param \Auth                          $auth
     * @param \BetaKiller\Helper\IFaceHelper $ifaceHelper
     */
    public function __construct(\Auth $auth, IFaceHelper $ifaceHelper)
    {
        $this->auth        = $auth;
        $this->ifaceHelper = $ifaceHelper;
    }

    /**
     * Action for logging in
     */
    public function action_login()
    {
        if (!$this->getRequest()->is_ajax()) {
            throw new HTTP_Exception_400('AJAX only gateway');
        }

        // Magic call for better exception handling
        $this->content_type_json();

        $user_login    = $this->getRequest()->post('user-login');
        $user_password = $this->getRequest()->post('user-password');
        $remember      = (bool)$this->getRequest()->post('remember');

        // Sanitize
        $user_login    = trim(HTML::chars($user_login));
        $user_password = trim(HTML::chars($user_password));

        if (!$user_login || !$user_password) {
            throw new HTTP_Exception_400('No username or password sent');
        }

        $this->auth->login($user_login, $user_password, $remember);

        // Возвращаем соответствующий ответ
        $this->send_json(Response::JSON_SUCCESS);
    }

    public function getData()
    {
        return [
            'login_url'          => $this->get_login_url(),
            'reset_password_url' => $this->get_reset_password_url(),
        ];
    }

    protected function get_login_url()
    {
        return $this->url('login');
    }

    protected function get_reset_password_url()
    {
        /** @var IFace_Auth_Password_Reset $iface */
        $iface = $this->ifaceHelper->createIFaceFromCodename('Auth_Password_Reset');

        return $iface->url();
    }
}
