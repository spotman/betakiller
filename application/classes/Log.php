<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Log
 * @deprecated Use Psr\Log\LoggerInterface and DI instead
 */
class Log extends Kohana_Log
{
    /**
     * @var  boolean  immediately write when logs are added (especially for CLI)
     */
    public static $write_on_add = TRUE;

    protected static function _add($level, $message, array $values = NULL, array $additional = NULL, $trace_level = 0)
    {
        if (is_object(Kohana::$log)) {
            Kohana::$log->add($level, $message, $values, $additional, ++$trace_level);
        }
    }

    public static function debug($message, array $values = NULL, array $additional = NULL, $trace_level = 0)
    {
        static::_add(self::DEBUG, $message, $values, $additional, ++$trace_level);
    }

    public static function info($message, array $values = NULL, array $additional = NULL, $trace_level = 0)
    {
        static::_add(self::INFO, $message, $values, $additional, ++$trace_level);
    }

    public static function notice($message, array $values = NULL, array $additional = NULL, $trace_level = 0)
    {
        static::_add(self::NOTICE, $message, $values, $additional, ++$trace_level);
    }

    public static function warning($message, array $values = NULL, array $additional = NULL, $trace_level = 0)
    {
        static::_add(self::WARNING, $message, $values, $additional, ++$trace_level);
    }

    public static function error($message, array $values = NULL, array $additional = NULL, $trace_level = 0)
    {
        static::_add(self::ERROR, $message, $values, $additional, ++$trace_level);
    }

    public static function exception(Exception $e)
    {
        Kohana_Exception::log($e);
    }
}
