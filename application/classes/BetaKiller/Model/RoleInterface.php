<?php
namespace BetaKiller\Model;

use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use Spotman\Acl\AclRoleInterface;

interface RoleInterface extends OrmInterface, AclRoleInterface
{
    // Model_Auth_Role methods (nothing special)

    // Extended methods

    /**
     * @return string
     */
    public function get_name();

    /**
     * Ищет глобальную роль по её имени
     *
     * @param $name
     * @return RoleInterface
     */
    public function get_by_name($name);

    /**
     * Returns filtered users relation
     *
     * @param bool $include_not_active
     * @return UserInterface
     */
    public function get_users($include_not_active = false);

    /**
     * Returns list of all roles IDs
     * Useful for getting all user`s roles IDs
     *
     * @return int[]
     */
    public function find_all_ids();

    /**
     * Returns "Developers" role object
     *
     * @return RoleInterface
     */
    public function developers();

    /**
     * Returns "Developers" role object
     *
     * @return RoleInterface
     */
    public function moderators();
}
