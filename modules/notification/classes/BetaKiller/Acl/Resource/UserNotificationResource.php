<?php
declare(strict_types=1);

namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\RoleInterface;
use Spotman\Acl\Resource\AbstractResolvingResource;

class UserNotificationResource extends AbstractResolvingResource
{
    public const ACTION_ENABLE_GROUP   = 'enableGroup';
    public const ACTION_DISABLE_GROUP  = 'disableGroup';
    public const ACTION_SET_GROUP_FREQ = 'setGroupFrequency';

    /**
     * Returns default permissions bundled with current resource
     * Key=>Value pairs where key is a permission identity and value is an array of roles
     * Useful for presetting permissions for resources with fixed access control list or permissions based on hard-coded logic
     *
     * @return string[][]
     */
    public function getDefaultAccessList(): array
    {
        return [
            self::ACTION_ENABLE_GROUP => [
                RoleInterface::LOGIN,
            ],

            self::ACTION_DISABLE_GROUP => [
                RoleInterface::LOGIN,
            ],

            self::ACTION_SET_GROUP_FREQ => [
                RoleInterface::LOGIN,
            ],
        ];
    }
}
