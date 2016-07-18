<?php defined('SYSPATH') OR die('No direct script access.');

class Log extends Kohana_Log
{
    /**
     * @var  boolean  immediately write when logs are added (especially for CLI)
     */
    public static $write_on_add = TRUE;

    protected static function _add($level, $message, array $values = NULL, array $additional = NULL)
    {
        if ( is_object(Kohana::$log) )
        {
            Kohana::$log->add($level, $message, $values, $additional);
        }
    }

    public static function debug($message, array $values = NULL)
    {
        static::_add(self::DEBUG, $message, $values);
    }

    public static function info($message, array $values = NULL)
    {
        static::_add(self::INFO, $message, $values);
    }

    public static function notice($message, array $values = NULL)
    {
        static::_add(self::NOTICE, $message, $values);
    }

    public static function warning($message, array $values = NULL)
    {
        static::_add(self::WARNING, $message, $values);
    }

    public static function error($message, array $values = NULL)
    {
        static::_add(self::ERROR, $message, $values);
    }

    public static function exception(Exception $e)
    {
        Kohana_Exception::log($e);
    }

}
