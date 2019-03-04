<?php
namespace BetaKiller\IFace\Auth;

use BetaKiller\Action\Auth\ChangePasswordAction;
use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\IFace\AbstractIFace;
use Psr\Http\Message\ServerRequestInterface;

class PasswordChangeIFace extends AbstractIFace
{
    public const FLASH_STATUS = 'password_changed';

    /**
     * @var \BetaKiller\Auth\UserUrlDetectorInterface
     */
    private $urlDetector;

    /**
     * PasswordChangeIFace constructor.
     *
     * @param \BetaKiller\Auth\UserUrlDetectorInterface $urlDetector
     */
    public function __construct(UserUrlDetectorInterface $urlDetector)
    {
        $this->urlDetector = $urlDetector;
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
        SessionHelper::checkToken($session);

        $urlHelper = ServerRequestHelper::getUrlHelper($request);
        $user      = ServerRequestHelper::getUser($request);

        $actionElement = $urlHelper->getUrlElementByCodename(ChangePasswordAction::codename());

        // Check was password really changed
        $isChanged = (bool)ServerRequestHelper::getFlash($request)->getFlash(self::FLASH_STATUS);

        return [
            'app_state' => [
                'actionUrl' => $urlHelper->makeUrl($actionElement),
                'nextUrl'   => $this->urlDetector->detect($user, $urlHelper),
                'isChanged' => $isChanged,
            ],
        ];
    }
}
