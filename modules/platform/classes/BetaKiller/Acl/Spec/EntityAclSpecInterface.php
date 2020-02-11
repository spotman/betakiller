<?php
declare(strict_types=1);

namespace BetaKiller\Acl\Spec;

use BetaKiller\Model\AbstractEntityInterface;
use Spotman\Acl\AclUserInterface;

interface EntityAclSpecInterface
{
    /**
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     * @param \Spotman\Acl\AclUserInterface             $user
     *
     * @return bool
     */
    public function isAllowedTo(AbstractEntityInterface $entity, AclUserInterface $user): bool;
}
