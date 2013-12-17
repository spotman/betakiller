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
    protected $_belongs_to = array(
        'parent'            =>  array(
            'model'         =>  'IFace',
            'foreign_key'   =>  'parent_id'
        )
    );

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
        return ORM::factory($this->object_name())
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
    public function get_uri()
    {
        return $this->get('uri');
    }

}