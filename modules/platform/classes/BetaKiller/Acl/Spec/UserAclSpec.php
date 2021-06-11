<?php
declare(strict_types=1);

namespace BetaKiller\Acl\Spec;

use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\UserInterface;
use Spotman\Acl\AclInterface;
use Spotman\Acl\AclUserInterface;

final class UserAclSpec implements EntityAclSpecInterface
{
    /**
     * @var \Spotman\Acl\AclInterface
     */
    private AclInterface $acl;

    /**
     * UserAclSpec constructor.
     */
    public function __construct(AclInterface $acl)
    {
        $this->acl = $acl;
    }

    /**
     * @inheritDoc
     */
    public function isAllowedTo(\BetaKiller\Model\EntityWithAclSpecInterface $entity, AclUserInterface $user): bool
    {
        if (!$entity instanceof UserInterface) {
            throw new \LogicException();
        }

        if (!$user instanceof UserInterface) {
            throw new \LogicException();
        }

        if ($this->acl->hasAssignedRoleName($user, RoleInterface::USER_MANAGEMENT)) {
            return true;
        }

        return $user->isEqualTo($entity);
    }
}
