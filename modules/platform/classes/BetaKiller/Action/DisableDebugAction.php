<?php
declare(strict_types=1);

namespace BetaKiller\Action;

use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\Model\RoleInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class DisableDebugAction extends AbstractAction
{
    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = ServerRequestHelper::getUser($request);

        if (!$user->hasRoleName(RoleInterface::DEVELOPER)) {
            throw new AccessDeniedException('Unauthorized debug mode');
        }

        $session = ServerRequestHelper::getSession($request);

        SessionHelper::disableDebug($session);

        return ResponseHelper::text('Debug disabled');
    }
}
