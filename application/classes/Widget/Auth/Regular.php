<?php defined('SYSPATH') OR die('No direct script access.');

class Widget_Auth_Regular extends Widget {

    public function action_login()
    {

    }

    /**
     * Generates HTML/CSS/JS representation of the widget
     * Implement this method in your widget
     * Use $this->send_string() / $this->send_json() / $this->send_jsonp() methods to populate output
     */
    protected function _render()
    {
        // TODO: Implement render() method.

        $view = $this->view();

        $view->set('login_url', $this->get_login_url());

        $view->set('reset_password_url', $this->get_reset_password_url());

        $this->send_view($view);
    }


    protected function get_login_url()
    {
        return $this->url('login');
    }

    protected function get_reset_password_url()
    {
        return IFace_Auth_ResetPassword::factory()->url();
    }

}