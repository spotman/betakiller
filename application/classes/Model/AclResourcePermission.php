<?php

class Model_AclResourcePermission extends \ORM
{
    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws Exception
     * @return void
     */
    protected function _initialize(): void
    {
        $this->_table_name = 'acl_resource_permissions';

        parent::_initialize();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->get('name');
    }
}
