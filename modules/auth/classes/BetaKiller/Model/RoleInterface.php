<?php
namespace BetaKiller\Model;

use Spotman\Acl\AclRoleInterface;

interface RoleInterface extends AbstractEntityInterface, AclRoleInterface, MultipleParentsTreeModelInterface
{
    public const URL_KEY = 'name';

    // Model_Auth_Role methods (nothing special)

    // Extended methods

    /**
     * Role for CLI scripts
     */
    public const CLI = 'cli';

    /**
     * Role for access to developer tools
     */
    public const DEVELOPER = 'developer';

    /**
     * Role with access to admin panel
     */
    public const ADMIN_PANEL = 'admin-panel';

    /**
     * Role with access to log in
     */
    public const LOGIN = 'login';

    /**
     * Role for unauthenticated users (guests)
     * Do not inherit in any other role (cause permission leakage through roles chain "a" -> "guest" <- "b")
     */
    public const GUEST = 'guest';

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
