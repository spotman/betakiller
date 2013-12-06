<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Model_IFace
 * @category   Models
 * @author     Kohana Team
 * @package    Betakiller
 */
class Model_IFace extends ORM {

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

    public static function find_by_codename($codename)
    {
        $model = static::factory('IFace')
            ->where('codename', '=', $codename)
            ->find();

        if ( ! $model->loaded() )
            throw new IFace_Exception('Can not find model for codename :codename', array(':codename' => $codename));

        return $model;
    }

    public function get_id()
    {
        return (int) $this->get('id');
    }

    public function get_parent_id()
    {
        return (int) $this->get('parent_id');
    }

    public function get_codename()
    {
        return $this->get('_codename');
    }

    public function get_url()
    {
        return $this->get('url');
    }

    public function is_default()
    {
        return (bool) $this->get('is_default');
    }

    /**
     * Returns parent iface`s model
     * @return Model_IFace|null
     */
    public function get_parent()
    {
        /** @var Model_IFace $parent */
        $parent = $this->get('parent');

        return $parent->loaded() ? $parent : NULL;
    }

}