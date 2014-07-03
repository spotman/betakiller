<?php defined('SYSPATH') OR die('No direct script access.');

class BetaKiller_Env
{
    /**
     * Shortcut method for Environment::instance()->$property.
     * Now you are able to call Env::get('property').
     * @static
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        return Environment::instance()->get($key);
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
        return static::get('acl');
    }

    /**
     * Хелпер для получения инстанса Auth
     * @return Auth_ORM
     */
    public static function auth()
    {
        return static::get('auth');
    }

    /**
     * Хелпер для получения текущего пользователя
     * @param bool $allow_guest
     * @return Model_User|NULL
     * @throws HTTP_Exception_401
     */
    public static function user($allow_guest = FALSE)
    {
        $user = static::get('user');

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
        return static::get('role');
    }

    /**
     * @return URL_Parameters
     */
    public static function url_parameters()
    {
        return static::get('url_parameters');
    }

}