<?php
namespace BetaKiller\Model;

class Role extends AbstractOrmBasedMultipleParentsTreeModel implements RoleInterface
{
    protected function getTreeModelThroughTableName()
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

    public function getName(): string
    {
        return $this->get('name');
    }

    /**
     * Returns the string identifier of the Role
     *
     * @return string
     */
    public function getRoleId(): string
    {
        return $this->getName();
    }
}
