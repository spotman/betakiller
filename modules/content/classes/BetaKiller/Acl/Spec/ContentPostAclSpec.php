<?php
declare(strict_types=1);

namespace BetaKiller\Acl\Spec;

use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\ContentPostInterface;
use Spotman\Acl\AclUserInterface;

final class ContentPostAclSpec implements EntityAclSpecInterface
{
    /**
     * @inheritDoc
     */
    public function isAllowedTo(AbstractEntityInterface $entity, AclUserInterface $user): bool
    {
        if (!$entity instanceof ContentPostInterface) {
            throw new \LogicException();
        }

        return true;
    }
}
