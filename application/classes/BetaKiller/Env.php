<?php defined('SYSPATH') OR die('No direct script access.');

class BetaKiller_Env
{
    /**
     * Shortcut method for Environment::instance()->$property.
     * Now you are able to call Env::get('property').
     * @static
     * @param string $key
     * @param string $act_as lets you to act as any module
     * @return mixed
     */
    public static function get($key, $act_as = NULL)
    {
        return Environment::instance()->get($key, $act_as);
    }

    /**
     * Shortcut method for Environment::instance()->set($key, $object)
     * @static
     * @param string $key
     * @param mixed $object
     * @return mixed
     */
    public static function set($key, $object)
    {
        Environment::instance()->set($key, $object);
    }

    /**
     * Хелпер для получения инстанса ACL
     * @return ACL
     */
    public static function acl()
    {
        return self::get('acl');
    }

    /**
     * Хелпер для получения инстанса Auth
     * @return Auth_ORM
     */
    public static function auth()
    {
        return self::get('auth');
    }

    /**
     * Хелпер для получения текущего пользователя
     * @param bool $allow_guest
     * @return Model_User|NULL
     * @throws HTTP_Exception_401
     */
    public static function user($allow_guest = FALSE)
    {
        $user = self::get('user');

        if ( $user === NULL AND ! $allow_guest )
            throw new HTTP_Exception_401('Authorization required!');

        return $user;
    }

    /**
     * @todo выпилить вместе с кривыми ролями
     * Хелпер для получения роли текущего пользователя
     * @return mixed
     */
    public static function role()
    {
        return self::get('role');
    }

}