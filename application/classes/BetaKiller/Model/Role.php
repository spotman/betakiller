<?php
namespace BetaKiller\Model;

use Model_Auth_Role;

class Role extends Model_Auth_Role implements RoleInterface
{
    const DEVELOPERS_ROLE_NAME  = 'developer';
    const MODERATORS_ROLE_NAME  = 'moderator';
    const WRITER_ROLE_NAME      = 'writer';

    public function get_name()
    {
        return $this->get('name');
    }

    /**
     * Ищет глобальную роль по её имени
     *
     * @param string $name
     *
     * @return RoleInterface
     */
    public function get_by_name($name)
    {
        return $this->where("name", "=", $name)->find();
    }

    /**
     * Returns filtered users relation
     *
     * @param bool $include_not_active
     *
     * @return UserInterface
     */
    public function get_users($include_not_active = FALSE)
    {
        $users = $this->get_users_relation();

        if (!$include_not_active)
            $users->filter_active();

        return $users;
    }

    /**
     * Returns relation for users with current role
     *
     * @return UserInterface
     */
    protected function get_users_relation()
    {
        return $this->get('users');
    }

    /**
     * Returns list of all roles IDs
     * Useful for getting all user`s roles IDs
     *
     * @return int[]
     */
    public function find_all_ids()
    {
        return $this->cached()->find_all()->as_array(NULL, $this->primary_key());
    }

    /**
     * Returns "Developers" role object
     *
     * @return RoleInterface
     */
    public function developers()
    {
        return $this->get_by_name(self::DEVELOPERS_ROLE_NAME);
    }

    /**
     * Returns "Developers" role object
     *
     * @return RoleInterface
     */
    public function moderators()
    {
        return $this->get_by_name(self::MODERATORS_ROLE_NAME);
    }

    /**
     * Returns the string identifier of the Role
     *
     * @return string
     */
    public function getRoleId()
    {
        return $this->get_name();
    }
}
