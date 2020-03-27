<?php
declare(strict_types=1);

namespace BetaKiller\Acl;

use BetaKiller\Model\AbstractEntityInterface;
use Spotman\Acl\AclUserInterface;

interface EntityPermissionResolverInterface
{
    /**
     * @param \Spotman\Acl\AclUserInterface             $user
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     * @param string                                    $action
     *
     * @param bool|null                                 $skipSpecCheck
     *
     * @return bool
     */
    public function isAllowed(
        AclUserInterface $user,
        AbstractEntityInterface $entity,
        string $action,
        bool $skipSpecCheck = null
    ): bool;
}
