<?php
namespace BetaKiller\Model;

use Spotman\Acl\AclRoleInterface;

interface RoleInterface extends AbstractEntityInterface, AclRoleInterface, MultipleParentsTreeModelInterface
{
    public const URL_KEY = 'name';

    // Model_Auth_Role methods (nothing special)

    // Extended methods

    /**
     * Root role for super-administrators, inherits all other roles
     */
    public const ROOT_ROLE_NAME = 'root';

    /**
     * Role for access to developer tools
     */
    public const DEVELOPER_ROLE_NAME = 'developer';

    /**
     * Simplified role for moderators
     */
    public const MODERATOR_ROLE_NAME = 'moderator';

    /**
     * Role with access to admin panel
     */
    public const ADMIN_PANEL_ROLE_NAME = 'admin';

    /**
     * Role with access to log in
     */
    public const LOGIN_ROLE_NAME = 'login';

    /**
     * Role for unauthenticated users (guests)
     */
    public const GUEST_ROLE_NAME = 'guest';

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\RoleInterface
     */
    public function setName(string $value): RoleInterface;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\RoleInterface
     */
    public function setDescription(string $value): RoleInterface;
}
