<?php defined('SYSPATH') OR die('No direct script access.');

class Model_Waiting extends ORM {

    protected function _initialize()
    {
        $this->belongs_to(array(
            'key'   =>  array(
                'model'         =>  'Waiting_Key',
                'foreign_key'   =>  'key_id',
            ),
        ));

        parent::_initialize();
    }

    public function rules()
    {
        return array(
            'key_id'    =>  array(
                array('not_empty'),
            ),

            'email'     =>  array(
                array('not_empty'),
                array('email'),
            ),
        );
    }

    /**
     * @return Model_Waiting_Key $model
     */
    public function get_key()
    {
        return $this->get('key');
    }

    public function get_key_name()
    {
        return $this->get_key()->get_name();
    }

    /**
     * @param Model_Waiting_Key $model
     * @return $this
     */
    public function set_key(Model_Waiting_Key $model)
    {
        return $this->set('key', $model);
    }

    public function get_email()
    {
        return $this->get('email');
    }

    /**
     * @param $value
     * @return $this
     */
    public function set_email($value)
    {
        return $this->set('email', $value);
    }


    public function check_duplicate()
    {
        $duplicate = ORM::factory($this->object_name())
            ->where('key_id', '=', $this->get('key_id'))
            ->where('email', '=', $this->get('email'))
            ->find();

        return ($duplicate->loaded() AND $duplicate->pk());
    }

//
//    public function autocomplete($query)
//    {
//        return $this->_autocomplete($query, array('label'));
//    }
//
//    protected function autocomplete_formatter()
//    {
//        return array(
//            'id'    =>  $this->get_id(),
//            'text'  =>  $this->get_label(),
//        );
//    }
//
//    public function get_api_response_data()
//    {
//        return array(
//            'id'    =>  $this->get_id(),
//            'label' =>  $this->get_label(),
//        );
//    }


}