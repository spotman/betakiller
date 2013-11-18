<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Model_Kohana_Location
 * @category   Models
 * @author     Spotman
 * @package    Betakiller
 */
class Model_Kohana_Location extends ORM {

    public $_table_name = "locations";

    public function get_id()
    {
        return $this->loaded() ? (int) $this->id : NULL;
    }

    public function get_parent_id()
    {
        return $this->loaded() ? $this->parent_id : NULL;
    }

    public function get_codename()
    {
        return $this->loaded() ? $this->codename : NULL;
    }

    public function get_url()
    {
        return $this->loaded() ? $this->url : NULL;
    }

    public function is_default()
    {
        return $this->loaded() ? (bool) $this->is_default : NULL;
    }

    /**
     * @return Database_Result|Model_Location[]
     */
    public function get_childs()
    {
        return ORM::factory('Location')
            ->where("parent_id", "=", $this->get_id())
            // TODO ->cached(60)
            ->find_all();
    }

}