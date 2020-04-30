<?php
declare(strict_types=1);

namespace BetaKiller\Acl\Spec;

use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\UserInterface;
use Spotman\Acl\AclUserInterface;

final class UserAclSpec implements EntityAclSpecInterface
{
    /**
     * @inheritDoc
     */
    public function isAllowedTo(AbstractEntityInterface $entity, AclUserInterface $user): bool
    {
        if (!$entity instanceof UserInterface) {
            throw new \LogicException();
        }

        if (!$user instanceof UserInterface) {
            throw new \LogicException();
        }

        if ($user->hasRoleName(RoleInterface::USER_MANAGEMENT)) {
            return true;
        }

        return $user->isEqualTo($entity);
    }
}
