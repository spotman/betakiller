<?php defined('SYSPATH') OR die('No direct script access.');

class BetaKiller_Environment extends Util_Registry_Class
{
    use Util_Instance_Singleton;

    public function get($key)
    {
        if ( ! $this->__isset($key) )
        {
            // Use custom function to get value
            $method = 'get_'.$key;
            $value = NULL;

            if ( method_exists(__CLASS__, $method) )
            {
                $this->set($key, $this->$method());
            }
        }

        return parent::get($key);
    }

    private function get_auth()
    {
        return Auth::instance();
    }

    private function get_user()
    {
        /** @var Auth $auth */
        $auth = $this->get('auth');
        return $auth->get_user();
    }

    private function get_module()
    {
        return Request::current()->module();
    }

    private function get_url_parameters()
    {
        return URL_Dispatcher::instance()->parameters();
    }

    private function get_url_dispatcher()
    {
        return URL_Dispatcher::instance();
    }

}
