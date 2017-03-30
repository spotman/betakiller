<?php
namespace BetaKiller\Model;

use BetaKiller\Utils\Kohana\TreeModelMultipleParentsOrm;

class Role extends TreeModelMultipleParentsOrm implements RoleInterface
{
    const GUEST_ROLE_NAME       = 'guest';
    const LOGIN_ROLE_NAME       = 'login';
    const ADMIN_ROLE_NAME       = 'admin';
    const DEVELOPER_ROLE_NAME   = 'developer';
    const MODERATOR_ROLE_NAME   = 'moderator';
    const WRITER_ROLE_NAME      = 'writer';

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function _initialize()
    {
        $this->has_many([
            'users' => [
                'model' => 'User',
                'through' => 'roles_users'
            ],
        ]);

        parent::_initialize();
    }

    protected function get_through_table_name()
    {
        return 'roles_inheritance';
    }

    public function rules()
    {
        return array(
            'name' => array(
                array('not_empty'),
                array('min_length', array(':value', 4)),
                array('max_length', array(':value', 32)),
            ),
            'description' => array(
                array('max_length', array(':value', 255)),
            )
        );
    }

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
        return $this->model_factory()->where($this->object_column("name"), "=", $name)->find();
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
     * Returns "Developers" role object
     *
     * @return RoleInterface
     */
    public function get_developer_role()
    {
        return $this->get_by_name(self::DEVELOPER_ROLE_NAME);
    }

    /**
     * Returns "Developers" role object
     *
     * @return RoleInterface
     */
    public function get_moderator_role()
    {
        return $this->get_by_name(self::MODERATOR_ROLE_NAME);
    }

    /**
     * @inheritDoc
     */
    public function get_guest_role()
    {
        return $this->get_by_name(self::GUEST_ROLE_NAME);
    }

    /**
     * @inheritDoc
     */
    public function get_login_role()
    {
        return $this->get_by_name(self::LOGIN_ROLE_NAME);
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

    /**
     * Place here additional query params
     */
    protected function additional_tree_model_filtering()
    {
        // Nothing to do
    }
}
