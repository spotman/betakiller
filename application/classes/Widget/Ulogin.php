<?php defined('SYSPATH') OR die('No direct script access.');

class Widget_Ulogin extends Widget {

    public function action_show()
    {
        // TODO
        if ( ! Env::user(TRUE) )
        {
            $uLogin = $this->ulogin_factory();

            $this->send_string( $uLogin->render() );
        }
        else
        {
            $this->send_string('Logged in');
        }
    }

    public function action_login()
    {
        $uLogin = $this->ulogin_factory();

        try
        {
            $uLogin->login();
        }
        catch ( Exception $e )
        {
            throw $e;
            // TODO
            //throw new HTTP_Exception_401;
        }
    }

    protected function ulogin_factory()
    {
        return Ulogin::factory()
            ->set_redirect_uri($this->url('login'));
    }

}