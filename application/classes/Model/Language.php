<?php

class Model_Language extends ORM
{
    protected $_table_name = 'languages';

    public function get_name()
    {
        return $this->loaded() ? $this->get('name') : NULL;
    }
}
