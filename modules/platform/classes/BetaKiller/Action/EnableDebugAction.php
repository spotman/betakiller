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

final class EnableDebugAction extends AbstractAction
{
    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = ServerRequestHelper::getSession($request);

        if (!SessionHelper::hasRoleName($session, RoleInterface::DEVELOPER)) {
            throw new AccessDeniedException('Unauthorized debug mode');
        }

        SessionHelper::enableDebug($session);

        return ResponseHelper::text('Debug enabled');
    }
}
