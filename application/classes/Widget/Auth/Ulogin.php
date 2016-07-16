<?php defined('SYSPATH') OR die('No direct script access.');

use \BetaKiller\IFace\Widget;

class Widget_Auth_Ulogin extends Widget {

    public function get_data()
    {
        $instance = $this->ulogin_factory();

        $auth_callback = $instance->get_widget_id().'_auth_callback';
        $instance->set_javascript_callback($auth_callback);

        return array(
            'token_login_url'   =>  $instance->get_redirect_uri(),
            'auth_callback'     =>  $auth_callback,
            'ulogin_view'       =>  $instance->render(),
        );
    }

    public function action_auth()
    {
        $this->content_type_json();

        $uLogin = $this->ulogin_factory();

        try
        {
            $uLogin->login();
            $this->send_json();
        }
        catch ( Ulogin_Exception $e )
        {
//            $this->send_json(self::JSON_ERROR, $e->getMessage());
            throw $e;
        }
        catch ( Exception $e )
        {
//            $this->send_json(self::JSON_ERROR, $e->getMessage());
            throw $e;
//            Kohana_Exception::_handler($e);
//            $this->send_json(self::JSON_ERROR);
        }
    }

    /**
     * @return Ulogin
     */
    protected function ulogin_factory()
    {
        return Ulogin::factory()
            ->set_redirect_uri($this->url('auth'));
    }

}
