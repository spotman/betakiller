<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_Console extends Controller_Developer
{
    public function action_show()
    {
        $view = $this->view();

        $view->set('apiURL', API::client()->get_url());

        $this->send_view($view);
    }
}
