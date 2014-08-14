<?php defined('SYSPATH') OR die('No direct script access.');

class Widget_Auth_Regular extends Widget {

    /**
     * Action for logging in
     */
    public function action_login()
    {
        if ( ! $this->request->is_ajax() )
        {
            throw new HTTP_Exception_400;
        }

        // Magic call for better exception handling
        $this->content_type_json();

        $user_login     = $this->request()->post("user-login");
        $user_password  = $this->request()->post("user-password");

        if ( ! $user_login OR ! $user_password )
        {
            throw new HTTP_Exception_400;
        }

        // TODO валидация данных перед проверкой

        Env::auth()->login($user_login, $user_password);

        // Возвращаем соответствующий ответ
        $this->send_json(Response::JSON_SUCCESS);
    }

    public function get_data()
    {
        return array(
            'login_url'             =>  $this->get_login_url(),
            'reset_password_url'    =>  $this->get_reset_password_url(),
        );
    }

    protected function get_login_url()
    {
        return $this->url('login');
    }

    protected function get_reset_password_url()
    {
        /** @var IFace_Auth_Password_Reset $iface */
        $iface = IFace::by_codename('Auth_Password_Reset');
        return $iface->url();
    }

}
