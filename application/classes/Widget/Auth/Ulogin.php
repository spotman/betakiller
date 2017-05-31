<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\IFace\Widget\AbstractBaseWidget;

class Widget_Auth_Ulogin extends AbstractBaseWidget
{
    public function getData()
    {
        $instance = $this->ulogin_factory();

        $auth_callback = 'ulogin_auth_callback';
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

        try {
            $uLogin->login();
            $this->send_json();
        } catch (Ulogin_Exception $e) {
            throw $e;
        } catch ( Exception $e ) {
            throw $e;
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
