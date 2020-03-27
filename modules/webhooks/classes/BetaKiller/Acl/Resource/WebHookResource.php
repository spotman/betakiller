<?php
declare(strict_types=1);

namespace BetaKiller\Acl\Resource;

use BetaKiller\CrudlsActionsInterface;
use BetaKiller\Model\RoleInterface;

final class WebHookResource extends AbstractEntityRelatedAclResource
{
    /**
     * @inheritDoc
     */
    public function getDefaultAccessList(): array
    {
        return [
            CrudlsActionsInterface::ACTION_READ => [
                RoleInterface::GUEST,
                // For internal checks
                RoleInterface::LOGIN,
            ],
        ];
    }
}
