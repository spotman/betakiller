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
use Spotman\Acl\AclInterface;

final class EnableDebugAction extends AbstractAction
{
    /**
     * @var \Spotman\Acl\AclInterface
     */
    private AclInterface $acl;

    /**
     * EnableDebugAction constructor.
     *
     * @param \Spotman\Acl\AclInterface $acl
     */
    public function __construct(AclInterface $acl)
    {
        $this->acl = $acl;
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = ServerRequestHelper::getSession($request);
        $user    = ServerRequestHelper::getUser($request);

        if (!$this->acl->hasAssignedRoleName($user, RoleInterface::DEVELOPER)) {
            throw new AccessDeniedException('Unauthorized debug mode');
        }

        SessionHelper::enableDebug($session);

        return ResponseHelper::text('Debug enabled');
    }
}
