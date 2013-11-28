<?php defined('SYSPATH') OR die('No direct script access.');

class Model_Role extends Model_Auth_Role
{
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
}