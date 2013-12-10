<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Model_IFace
 * @category   Models
 * @author     Spotman
 * @package    Betakiller
 */
class Model_IFace extends ORM implements IFace_Model {

    public $_table_name = "ifaces";

    /**
     * "Has one" relationships
     * @var array
     */
    protected $_has_one = array(
        'parent'            =>  array(
            'model'         =>  'IFace',
            'foreign_key'   =>  'parent_id'
        )
    );

//    /**
//     * Provider factory (for current model type)
//     *
//     * @return IFace_Provider
//     */
//    protected function get_provider()
//    {
//        return IFace_Provider_Source::factory('DB');
//    }

    public function get_id()
    {
        return (int) $this->get('id');
    }

    public function get_parent_id()
    {
        return (int) $this->get('parent_id');
    }

    /**
     * Returns list of child iface models
     *
     * @return IFace_Model[]
     */
    function get_children()
    {
        ORM::factory($this->_object_name)
            ->where('parent_id', '=', $this->pk())
            ->find_all()
            ->as_array();
    }

    /**
     * Return parent iface model or NULL
     *
     * @return IFace_Model[]
     */
    public function get_parent()
    {
        /** @var Model_IFace $parent */
        $parent = $this->get('parent');
        return $parent->loaded() ? $parent : NULL;
    }

    /**
     * Returns TRUE if iface is marked as "default"
     *
     * @return bool
     */
    public function is_default()
    {
        return (bool) $this->get('is_default');
    }

    /**
     * Returns iface codename
     *
     * @return string
     */
    public function get_codename()
    {
        return $this->get('codename');
    }

    /**
     * Returns iface url part
     *
     * @return string
     */
    public function get_url()
    {
        return $this->get('url');
    }

}