<?php
declare(strict_types=1);

namespace BetaKiller\Acl\Spec;

use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\EntityWithAclSpecInterface;
use Spotman\Acl\AclUserInterface;

interface EntityAclSpecInterface
{
    /**
     * @param \BetaKiller\Model\EntityWithAclSpecInterface $entity
     * @param \Spotman\Acl\AclUserInterface                $user
     *
     * @return bool
     */
    public function isAllowedTo(EntityWithAclSpecInterface $entity, AclUserInterface $user): bool;
}
