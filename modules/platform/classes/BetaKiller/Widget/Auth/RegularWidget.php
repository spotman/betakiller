<?php
namespace BetaKiller\Widget\Auth;

use BetaKiller\Auth\AuthFacade;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\IFaceHelper;
use BetaKiller\IFace\Auth\PasswordReset;
use BetaKiller\Widget\AbstractPublicWidget;
use HTML;
use Psr\Http\Message\ServerRequestInterface;

class RegularWidget extends AbstractPublicWidget
{
    /**
     * @var AuthFacade
     */
    private $auth;

    /**
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $psrRequest;

    /**
     * RegularWidget constructor.
     *
     * @param \BetaKiller\Auth\AuthFacade              $auth
     * @param \BetaKiller\Helper\IFaceHelper           $ifaceHelper
     * @param \Psr\Http\Message\ServerRequestInterface $psrRequest
     */
    public function __construct(AuthFacade $auth, IFaceHelper $ifaceHelper, ServerRequestInterface $psrRequest)
    {
        $this->auth        = $auth;
        $this->ifaceHelper = $ifaceHelper;
        $this->psrRequest  = $psrRequest;
    }

    /**
     * Action for logging in
     *
     * @throws \BetaKiller\Exception\BadRequestHttpException
     */
    public function actionLogin(): void
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

        $user = $this->auth->login($userLogin, $userPassword, $this->psrRequest);

        if ($remember) {
            $this->auth->enableAutoLogin($user, $this->psrRequest);
        }

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

    private function getLoginUrl(): string
    {
        return $this->url('login');
    }

    private function getResetPasswordUrl(): string
    {
        /** @var PasswordReset $iface */
        $iface = $this->ifaceHelper->createIFaceFromCodename('Auth_PasswordReset');

        return $this->ifaceHelper->makeIFaceUrl($iface);
    }
}
