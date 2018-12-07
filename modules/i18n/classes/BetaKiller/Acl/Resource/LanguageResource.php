<?php
declare(strict_types=1);

namespace BetaKiller\Acl\Resource;

use BetaKiller\I18n\I18nFacade;
use BetaKiller\Model\RoleInterface;

class LanguageResource extends AbstractEntityRelatedAclResource
{
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
            self::ACTION_CREATE => [
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_READ => [
                I18nFacade::ROLE_TRANSLATOR,
            ],

            self::ACTION_UPDATE => [
                I18nFacade::ROLE_TRANSLATOR,
            ],

            self::ACTION_DELETE => [
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_LIST => [
                I18nFacade::ROLE_TRANSLATOR,
            ],

            self::ACTION_SEARCH => [
                RoleInterface::LOGIN,
            ],
        ];
    }

    protected function getActionsWithoutEntity(): array
    {
        // Entity is not required at all
        return array_keys($this->getDefaultAccessList());
    }
}
