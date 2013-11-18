<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Singleton
 *
 * Extend this class if you need Singleton object.
 * Use protected constructor if needed.
 *
 * Usage (client-code): CLASS::instance();
 */
abstract class Singleton {

    protected static $instance;

    /**
     * You can`t create Singleton objects directly, use CLASS::instance() instead
     * Also you can define your own protected constructor in child class
     */
    protected function __construct() {}

    private final function __clone() {}

    final public static function instance()
    {
        if ( ! self::$instance )
        {
            self::$instance = new static;
        }
        return self::$instance;
    }

}
