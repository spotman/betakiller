<?php
namespace BetaKiller\IFace\Admin;

use BetaKiller\Acl\Resource\AdminResource;
use BetaKiller\IFace\IFace;
use BetaKiller\Helper\CurrentUser;

abstract class AdminBase extends IFace
{
    use CurrentUser;

    protected $adminAclResource;

    public function __construct(AdminResource $adminResource)
    {
        $this->adminAclResource = $adminResource;
    }

    public function before()
    {
        if (!$this->check_iface_permissions())
            throw new \HTTP_Exception_403('Permission denied');
    }

    protected function check_iface_permissions()
    {
        // Force authorization
        $user = $this->current_user();

        $token = \Profiler::start('Acl', 'first load');

        $value = $this->adminAclResource->isEnabled() || $user->is_admin_allowed();

        \Profiler::stop($token);

        return $value;
    }

    public function getDefaultExpiresInterval()
    {
        $interval = new \DateInterval('PT1H');
        $interval->invert = 1;
        return $interval;
    }
}
