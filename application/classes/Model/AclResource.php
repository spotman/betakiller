<?php

use Spotman\Acl\ResourceInterface;
use BetaKiller\Utils\Kohana\TreeModelSingleParentOrm;

class Model_AclResource extends TreeModelSingleParentOrm implements ResourceInterface
{
    protected $_table_name = 'acl_resources';

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
    public function get_codename()
    {
        return $this->get('codename');
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return $this->get_codename();
    }

    public function getParentResourceId()
    {
        $parent = $this->get_parent();
        return $parent ? $parent->getResourceId() : null;
    }

    /**
     * @return $this[]
     */
    public function getAllResources()
    {
        return $this->get_all();
    }
}
