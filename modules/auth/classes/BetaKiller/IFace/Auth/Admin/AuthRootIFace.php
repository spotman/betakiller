<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Auth\Admin;

use BetaKiller\Action\Auth\Admin\SessionRestartAction;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use Psr\Http\Message\ServerRequestInterface;

class AuthRootIFace extends AbstractAdminIFace
{
    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $sessionRestartAction = $urlHelper->getUrlElementByCodename(SessionRestartAction::codename());

        return [
            'restart_session_url' => $urlHelper->makeUrl($sessionRestartAction),
        ];
    }
}
