<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Model_IFace
 * @category   Models
 * @author     Kohana Team
 * @package    Betakiller
 */
class Model_IFace extends ORM {

    public $_table_name = "ifaces";

    public function get_id()
    {
        return (int) $this->id;
    }

    public function get_parent_id()
    {
        return (int) $this->parent_id;
    }

    public function get_codename()
    {
        return $this->codename;
    }

    public function get_url()
    {
        return $this->url;
    }

    public function is_default()
    {
        return (bool) $this->is_default;
    }

}