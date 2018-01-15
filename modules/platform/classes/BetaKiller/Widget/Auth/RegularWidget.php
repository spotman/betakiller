<?php
namespace BetaKiller\Widget\Auth;

use Auth;
use BetaKiller\Helper\IFaceHelper;
use BetaKiller\IFace\Auth\PasswordReset;
use BetaKiller\IFace\Widget\AbstractBaseWidget;
use HTML;
use HTTP_Exception_400;

class RegularWidget extends AbstractBaseWidget
{
    /**
     * @var Auth
     */
    private $auth;

    /**
     * RegularWidget constructor.
     *
     * @param \Auth                          $auth
     * @param \BetaKiller\Helper\IFaceHelper $ifaceHelper
     */
    public function __construct(\Auth $auth, IFaceHelper $ifaceHelper)
    {
        parent::__construct();

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
        $this->send_success_json();
    }

    public function getData(): array
    {
        return [
            'login_url'          => $this->getLoginUrl(),
            'reset_password_url' => $this->getResetPasswordUrl(),
        ];
    }

    protected function getLoginUrl()
    {
        return $this->url('login');
    }

    protected function getResetPasswordUrl()
    {
        /** @var PasswordReset $iface */
        $iface = $this->ifaceHelper->createIFaceFromCodename('Auth_PasswordReset');

        return $iface->url();
    }
}
