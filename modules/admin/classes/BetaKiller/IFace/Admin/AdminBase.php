<?php
namespace BetaKiller\IFace\Admin;

use BetaKiller\Acl\Resource\AdminResource;
use BetaKiller\IFace\IFace;
use BetaKiller\Helper\CurrentUserTrait;
use Spotman\Acl\AccessResolver\AclAccessResolverInterface;

abstract class AdminBase extends IFace
{
    use CurrentUserTrait;

    /**
     * @Inject
     * @var AdminResource
     */
    protected $adminAclResource;

    /**
     * @Inject
     * @var AclAccessResolverInterface
     */
    private $resolver;

    public function before()
    {
        if (!$this->checkIfacePermissions()) {
            throw new \HTTP_Exception_403('Permission denied');
        }
    }

    /**
     * @todo deal with AclResourceResolver
     * @return bool
     */
    protected function checkIfacePermissions()
    {
        // Force authorization
        $user = $this->current_user();

        return $this->adminAclResource->useResolver($this->resolver)->isEnabled() || $user->is_admin_allowed();
    }

    public function getDefaultExpiresInterval()
    {
        $interval = new \DateInterval('PT1H');
        $interval->invert = 1;
        return $interval;
    }
}
