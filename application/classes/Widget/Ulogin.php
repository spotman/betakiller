<?php defined('SYSPATH') OR die('No direct script access.');

class Widget_Ulogin extends Widget {

    public function action_show()
    {
        if ( Env::user(TRUE) )
            return 'logged in';

        $uLogin = Ulogin::factory();

        if ( $uLogin->mode() )
        {
            $uLogin->login();

            throw new Kohana_Exception('ulogin login');
        }
        else
        {
            $this->response()->send_string($uLogin);
        }

        //$this->response()->send_json(Response::JSON_SUCCESS, $result);
    }

}