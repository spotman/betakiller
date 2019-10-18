<?php
namespace BetaKiller\IFace\Auth;

use BetaKiller\Action\Auth\SendRecoveryEmailAction;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\AbstractIFace;
use Psr\Http\Message\ServerRequestInterface;

class AccessRecoveryRequestIFace extends AbstractIFace
{
    public const FLASH_STATUS         = 'access_recovery_status';
    public const FLASH_STATUS_OK      = 'ok';
    public const FLASH_STATUS_MISSING = 'missing';

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

        $actionElement = $urlHelper->getUrlElementByCodename(SendRecoveryEmailAction::codename());

        $user = ServerRequestHelper::getUser($request);

        return [
            'app_state' => [
                'actionUrl' => $urlHelper->makeUrl($actionElement),
                'isOk'      => $status && $status === self::FLASH_STATUS_OK,
                'isMissing' => $status && $status === self::FLASH_STATUS_MISSING,
                'userEmail' => !$user->isGuest() ? $user->getEmail() : null,
            ],
        ];
    }
}
