<?php defined('SYSPATH') OR die('No direct script access.');

class Widget_Ulogin extends Widget {

    protected function render()
    {
        $instance = $this->ulogin_factory();
        $this->send_string($instance->render());
    }

    public function action_login()
    {
        $uLogin = $this->ulogin_factory();

        try
        {
            $uLogin->login();
        }
        catch ( Ulogin_Exception $e )
        {
            throw $e;
            // TODO
            //throw new HTTP_Exception_401;
        }
        catch ( Exception $e )
        {
            throw $e;
        }
    }

    protected function ulogin_factory()
    {
        return Ulogin::factory()
            ->set_redirect_uri($this->url('login'));
    }

}