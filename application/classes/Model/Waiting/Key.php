<?php defined('SYSPATH') OR die('No direct script access.');

class Model_Waiting_Key extends ORM {

    public function rules()
    {
        return array(
            'name'   =>  array(
                array('not_empty'),
            ),
        );
    }

    public function get_name()
    {
        return $this->get('name');
    }

    /**
     * @param $value
     * @return $this
     */
    public function set_name($value)
    {
        return $this->set('name', $value);
    }

}