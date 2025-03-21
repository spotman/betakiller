<?php

namespace BetaKiller\IFace\Auth;

use BetaKiller\Action\Auth\ChangePasswordAction;
use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\IFace\AbstractIFace;
use BetaKiller\Security\CsrfService;
use Psr\Http\Message\ServerRequestInterface;

readonly class PasswordChangeIFace extends AbstractIFace
{
    public const FLASH_STATUS = 'password_changed';

    /**
     * PasswordChangeIFace constructor.
     *
     * @param \BetaKiller\Auth\UserUrlDetectorInterface $urlDetector
     * @param \BetaKiller\Security\CsrfService          $csrf
     */
    public function __construct(private UserUrlDetectorInterface $urlDetector, private CsrfService $csrf)
    {
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        $session = ServerRequestHelper::getSession($request);

        // Ensure user had been authorized via one-time token
        SessionHelper::checkVerificationTokenExists($session);

        $urlHelper = ServerRequestHelper::getUrlHelper($request);
        $user      = ServerRequestHelper::getUser($request);

        // Check was password really changed
        $isChanged = (bool)ServerRequestHelper::getFlash($request)->getFlash(self::FLASH_STATUS);

        return [
            'app_state' => [
                'userName'  => $user->getFirstName(),
                'actionUrl' => $urlHelper->makeCodenameUrl(ChangePasswordAction::codename()),
                'nextUrl'   => $this->urlDetector->detect($user),
                'isChanged' => $isChanged,
                'csrf'      => !$isChanged ? $this->csrf->createRequestToken($request) : null,
            ],
        ];
    }
}
