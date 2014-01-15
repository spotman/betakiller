<?php defined('SYSPATH') OR die('No direct script access.');

class Model_Role extends Model_Auth_Role
{
    const DEVELOPERS_ROLE_NAME = 'developers';

    public function get_name()
    {
        return $this->name;
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
     * Returns all users with current role
     *
     * @return Model_User[]
     */
    public function get_users()
    {
        return $this->get('users');
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
}