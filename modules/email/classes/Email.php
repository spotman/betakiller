<?php defined('SYSPATH') or die('No direct script access.');

class Email extends Kohana_Email {

    protected static $_default_from;

    public static function send($from, $to, $subject, $message, $html = FALSE, $attach = FALSE)
    {
        if ($from === NULL)
        {
            $from = static::$_default_from ?: static::$_default_from = static::config()->get('from');
        }

        return parent::send($from, $to, $subject, $message, $html, $attach);
    }
}