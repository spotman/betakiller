<?php defined('SYSPATH') or die('No direct script access.');

class Email extends Kohana_Email {

    public static function send($to, $from = null, $subject, $message, $html = FALSE, $attach = FALSE)
    {
        if ($from === null) $from = 'inform@sentra.ru';

        return parent::send($to, $from, $subject, $message, $html, $attach);
    }
}