<?php

use BetaKiller\Utils\Kohana\TreeModelSingleParentOrm;

class Model_AclResource extends TreeModelSingleParentOrm
{
    protected function _initialize()
    {
        $this->_table_name = 'acl_resources';

        parent::_initialize();
    }

    /**
     * Place here additional query params
     */
    protected function additional_tree_model_filtering()
    {
        // Nothing to do
    }

    /**
     * @return string
     */
    public function getCodename()
    {
        return $this->get('codename');
    }


    public function getParentResourceCodename()
    {
        $parent = $this->get_parent();
        return $parent ? $parent->getCodename() : null;
    }

    /**
     * @return $this[]
     */
    public function getAllResources()
    {
        return $this->get_all();
    }
}
