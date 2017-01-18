<?php defined('SYSPATH') OR die('No direct script access.');

class Request extends BetaKiller\Utils\Kohana\Request
{
    public static function redirect($url)
    {
        HTTP::redirect($url);
    }

    public function module()
    {
        return $this->param('module');
    }

    public static function client_ip()
    {
        return static::$client_ip;
    }
}
