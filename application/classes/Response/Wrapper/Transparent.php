<?php defined('SYSPATH') OR die('No direct script access.');

class Response_Wrapper_Transparent extends Response_Wrapper {

    public function render()
    {
        return $this->get_content();
    }

}