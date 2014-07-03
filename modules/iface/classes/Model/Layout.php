<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Model_Layout
 *
 * @category   Models
 * @author     Spotman
 * @package    Betakiller
 */
class Model_Layout extends ORM {

    /**
     * @return static
     * @throws Kohana_Exception
     */
    public function get_default()
    {
        $default = $this->where('is_default', '=', TRUE)->cached()->find();

        if ( ! $default->loaded() )
            throw new Kohana_Exception('No default layout found; set it, please');

        return $default;
    }

    protected function _initialize()
    {
        $this->has_many(array(
            'iface'            =>  array(
                'model'         =>  'IFace',
                'foreign_key'   =>  'layout_id'
            ),
        ));

        parent::_initialize();
    }

    public function get_id()
    {
        return (int) $this->pk();
    }

    /**
     * Returns TRUE if layout is marked as "default"
     *
     * @return bool
     */
    public function is_default()
    {
        return (bool) $this->get('is_default');
    }

    /**
     * Returns layout codename (filename)
     *
     * @return string
     */
    public function get_codename()
    {
        return $this->get('codename');
    }

    /**
     * Returns layout title (human-readable name)
     *
     * @return string
     */
    public function get_title()
    {
        return $this->get('title');
    }

}