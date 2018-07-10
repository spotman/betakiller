<?php
namespace BetaKiller\Widget\Auth;

use Auth;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\IFaceHelper;
use BetaKiller\IFace\Auth\PasswordReset;
use BetaKiller\Widget\AbstractPublicWidget;
use HTML;

class RegularWidget extends AbstractPublicWidget
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
     *
     * @throws \BetaKiller\Exception\BadRequestHttpException
     */
    public function actionLogin()
    {
        if (!$this->getRequest()->is_ajax()) {
            throw new BadRequestHttpException('AJAX only gateway');
        }

        // Magic call for better exception handling
        $this->content_type_json();

        $userLogin    = $this->getRequest()->post('user-login');
        $userPassword = $this->getRequest()->post('user-password');
        $remember     = (bool)$this->getRequest()->post('remember');

        // Sanitize
        $userLogin    = trim(HTML::chars($userLogin));
        $userPassword = trim(HTML::chars($userPassword));

        if (!$userLogin || !$userPassword) {
            throw new BadRequestHttpException('No username or password sent');
        }

        $this->auth->login($userLogin, $userPassword, $remember);

        // Возвращаем соответствующий ответ
        $this->send_success_json();
    }

    public function getData(): array
    {
        return [
            'login_url' => $this->getLoginUrl(),
//            'reset_password_url' => $this->getResetPasswordUrl(),
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

        return $this->ifaceHelper->makeIFaceUrl($iface);
    }
}
