<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\Role;

class ContentPostResource extends AbstractStatusRelatedModelAclResource
{
    protected function getCreatePermissionRoles()
    {
        return [
            Role::ADMIN_ROLE_NAME,
            Role::MODERATOR_ROLE_NAME,
            Role::DEVELOPER_ROLE_NAME,
            Role::WRITER_ROLE_NAME,
        ];
    }
}
