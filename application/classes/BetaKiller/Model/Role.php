<?php defined('SYSPATH') OR die('No direct script access.');

class BetaKiller_Model_Role extends Model_Auth_Role
{
    const DEVELOPERS_ROLE_NAME  = 'developer';
    const MODERATORS_ROLE_NAME   = 'moderator';

    public function get_name()
    {
        return $this->get('name');
    }

    /**
     * Ищет глобальную роль по её имени
     * @param $name
     * @return Model_Role
     */
    public function get_by_name($name)
    {
        return $this->where("name", "=", $name)->find();
    }

    /**
     * Returns relation for users with current role
     *
     * @return Model_User
     */
    public function get_users()
    {
        return $this->get('users');
    }

    /**
     * Returns list of all roles IDs
     * Useful for getting all user`s roles IDs
     *
     * @return array
     */
    public function find_all_ids()
    {
        return $this->cached()->find_all()->as_array(NULL, $this->primary_key());
    }

    /**
     * Returns "Developers" role object
     *
     * @return Model_Role
     */
    public function developers()
    {
        return $this->get_by_name(self::DEVELOPERS_ROLE_NAME);
    }

    /**
     * Returns "Developers" role object
     *
     * @return Model_Role
     */
    public function moderators()
    {
        return $this->get_by_name(self::MODERATORS_ROLE_NAME);
    }
}
