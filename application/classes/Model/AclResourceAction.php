<?php

class Model_AclResourceAction extends \ORM
{
    protected $_table_name = 'acl_resource_actions';

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws Exception
     * @return void
     */
    protected function _initialize()
    {
        $this->belongs_to([
            'resource' => [
                'model'         =>  'AclResource',
                'foreign_key'   =>  'resource_id'
            ],
        ]);


        $this->load_with(['resource']);

        parent::_initialize();
    }

    /**
     * @return string
     */
    public function get_name()
    {
        return $this->get('name');
    }

    /**
     * @return string
     */
    public function get_resource_identity()
    {
        return $this->get_resource_relation()->getResourceId();
    }

    /**
     * @return Model_AclResource
     */
    protected function get_resource_relation()
    {
        return $this->get('resource');
    }
}
