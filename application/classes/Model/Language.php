<?php defined('SYSPATH') OR die('No direct script access.');

class Model_Language extends ORM
{
    protected $_table_name = 'languages';

    public function get_name()
    {
        return $this->loaded() ? $this->get('name') : NULL;
    }
}
