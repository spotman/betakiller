<?php
namespace BetaKiller\IFace\Admin;

use BetaKiller\Acl\Resource\AdminResource;
use BetaKiller\IFace\IFace;
use BetaKiller\Helper\CurrentUserTrait;
use Spotman\Acl\Resolver\AccessResolverInterface;

abstract class AdminBase extends IFace
{
    use CurrentUserTrait;

    protected $adminAclResource;

    public function __construct(AdminResource $adminResource, AccessResolverInterface $resolver)
    {
        $this->adminAclResource = $adminResource->useResolver($resolver);
    }

    public function before()
    {
        if (!$this->checkIfacePermissions()) {
            throw new \HTTP_Exception_403('Permission denied');
        }
    }

    protected function checkIfacePermissions()
    {
        // Force authorization
        $user = $this->current_user();

        return $this->adminAclResource->isEnabled() || $user->is_admin_allowed();
    }

    public function getDefaultExpiresInterval()
    {
        $interval = new \DateInterval('PT1H');
        $interval->invert = 1;
        return $interval;
    }
}
