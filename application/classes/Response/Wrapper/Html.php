<?php defined('SYSPATH') OR die('No direct script access.');

class Response_Wrapper_Html extends Response_Wrapper {

    public function render()
    {
        $view = $this->view();
        $view->set_content($this->get_content());
        return $view->render();
    }

}