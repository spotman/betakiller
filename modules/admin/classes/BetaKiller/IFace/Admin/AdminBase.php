<?php
namespace BetaKiller\IFace\Admin;

use BetaKiller\Acl\Resource\AdminResource;
use BetaKiller\IFace\IFace;
use BetaKiller\Helper\CurrentUserTrait;

abstract class AdminBase extends IFace
{
    use CurrentUserTrait;

    /**
     * @Inject
     * @var \Spotman\Acl\AclInterface
     */
    protected $acl;

    /**
     * @Inject
     * @var \BetaKiller\Model\UserInterface
     */
    protected $user;

    public function before()
    {
        $this->user->forceAuthorization();

        if (!$this->checkIfacePermissions()) {
            throw new \HTTP_Exception_403('Permission denied');
        }
    }

    /**
     * @return bool
     * TODO remove after implementing centralized IFace access control
     */
    protected function checkIfacePermissions()
    {
        /** @var AdminResource $adminAclResource */
        $adminAclResource = $this->acl->getResource('Admin');

        return $adminAclResource->isEnabled();
    }

    public function getDefaultExpiresInterval()
    {
        $interval = new \DateInterval('PT1H');
        $interval->invert = 1;
        return $interval;
    }
}
