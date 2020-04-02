<?php
namespace BetaKiller\IFace\Auth;

use BetaKiller\Action\Auth\SendRecoveryEmailAction;
use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\AbstractIFace;
use BetaKiller\Security\CsrfService;
use Psr\Http\Message\ServerRequestInterface;

class AccessRecoveryRequestIFace extends AbstractIFace
{
    public const FLASH_STATUS         = 'access_recovery_status';
    public const FLASH_STATUS_OK      = 'ok';
    public const FLASH_STATUS_BLOCKED = 'blocked';
    public const FLASH_STATUS_MISSING = 'missing';

    /**
     * @var \BetaKiller\Security\CsrfService
     */
    private $csrf;

    /**
     * @var \BetaKiller\Auth\UserUrlDetectorInterface
     */
    private $urlDetector;

    /**
     * AccessRecoveryRequestIFace constructor.
     *
     * @param \BetaKiller\Security\CsrfService $csrf
     */
    public function __construct(CsrfService $csrf, UserUrlDetectorInterface $urlDetector)
    {
        $this->csrf        = $csrf;
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
        $urlHelper = ServerRequestHelper::getUrlHelper($request);
        $status    = ServerRequestHelper::getFlash($request)->getFlash(self::FLASH_STATUS);

        $user = ServerRequestHelper::getUser($request);

        return [
            'app_state' => [
                'actionUrl' => $urlHelper->makeCodenameUrl(SendRecoveryEmailAction::codename()),
                'isOk'      => $status && $status === self::FLASH_STATUS_OK,
                'isMissing' => $status && $status === self::FLASH_STATUS_MISSING,
                'isBlocked' => $status && $status === self::FLASH_STATUS_BLOCKED,
                'userEmail' => !$user->isGuest() ? $user->getEmail() : null,
                'csrf'      => $this->csrf->createRequestToken($request),
                'nextUrl'   => $this->urlDetector->detect($user),
            ],
        ];
    }
}
