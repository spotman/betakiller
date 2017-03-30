<?php
namespace BetaKiller\IFace\Admin;

use BetaKiller\Acl\Resource\AdminResource;
use BetaKiller\IFace\IFace;
use BetaKiller\Helper\CurrentUserTrait;
use Spotman\Acl\Resolver\UserAccessResolver;

abstract class AdminBase extends IFace
{
    use CurrentUserTrait;

    protected $adminAclResource;

    /**
     * @var \Spotman\Acl\Resolver\UserAccessResolver
     */
    protected $userAccessResolver;

    public function __construct(AdminResource $adminResource, UserAccessResolver $resolver)
    {
        $this->adminAclResource = $adminResource->useResolver($resolver);
    }

    public function before()
    {
        if (!$this->check_iface_permissions()) {
            throw new \HTTP_Exception_403('Permission denied');
        }
    }

    protected function check_iface_permissions()
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
