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
    public const ADMIN_ROLE_NAME = 'admin';

    /**
     * Role with access to log in
     */
    public const LOGIN_ROLE_NAME = 'login';

    /**
     * Role for unauthenticated users (guests)
     */
    public const GUEST_ROLE_NAME = 'guest';

    /**
     *
     */
    public const EMPLOYER_ROLE_NAME = 'employer';

    /**
     *
     */
    public const APPLICANT_ROLE_NAME = 'applicant';

    /**
     *
     */
    public const SCOUT_ROLE_NAME = 'scout';

    /**
     * @return string
     */
    public function getName(): string;
}
