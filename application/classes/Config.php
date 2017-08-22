<?php

class Config extends Kohana_Config
{
    // TODO remove
    public function drop_cache()
    {
        $this->_groups = array();
    }
}
