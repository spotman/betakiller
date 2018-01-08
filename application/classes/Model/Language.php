<?php

class Model_Language extends ORM
{
    protected $_table_name = 'languages';

    public function getName()
    {
        return $this->loaded() ? $this->get('name') : null;
    }
}
