<?php
namespace BetaKiller\Model;

use Spotman\Acl\AclRoleInterface;

interface RoleInterface extends AbstractEntityInterface, AclRoleInterface, MultipleParentsTreeModelInterface
{
    public const URL_KEY = 'name';

    // Model_Auth_Role methods (nothing special)

    // Extended methods
    public const DEVELOPER_ROLE_NAME = 'developer';
    public const WRITER_ROLE_NAME    = 'writer';
    public const MODERATOR_ROLE_NAME = 'moderator';
    public const ADMIN_ROLE_NAME     = 'admin';
    public const LOGIN_ROLE_NAME     = 'login';
    public const GUEST_ROLE_NAME     = 'guest';

    /**
     * @return string
     */
    public function getName(): string;
}
