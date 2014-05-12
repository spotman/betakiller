<?php defined('SYSPATH') OR die('No direct script access.');

class BetaKiller_Environment
{
    use Util_Singleton;

    /**
     * Defined variables by modules ( '<module_name>' => array('<key1>', '<key2>') )
     * @var array
     */
    protected static $vars = array(
        'test' => array(
            'test',
        ),
    );

    /**
     * Accordance between module name and class name
     * @var array
     */
    protected static $classes = array(
        'test' => 'Test',
    );

    /**
     * @var Util_Registry_Class
     */
    protected $registry;

    protected function __construct()
    {
        $this->registry = new Util_Registry_Class;
    }


    public static function get($key, $act_as = NULL)
    {
        $instance = static::instance();

        if ( ! $instance->registry->__isset($key) )
        {
            // Use custom function to get value
            $method = 'get_'.$key;

            if ( method_exists(__CLASS__, $method) )
            {
                $instance->set($key, $instance->$method());
            }
            else
            {
                $module = ( $act_as === NULL ) ? $instance->get_module() : $act_as;

                if ( isset(self::$vars[$module]) AND in_array($key, self::$vars[$module]) )
                {
                    $class = self::$classes[$module];
                    $instance->set($key, $class::$method());
                }
            }
        }

        return $instance->registry->get($key);
    }

    public static function set($key, $object)
    {
        static::instance()->registry->set($key, $object);
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

    /**
     * @todo fix
     * Получение объекта ACL по умолчанию
     * @return ACL
     */
    public function get_acl()
    {
        return ACL::factory($this->get_module());
    }
}