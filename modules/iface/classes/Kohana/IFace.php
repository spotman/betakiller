<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Base IFace class for Kohana framework
 */
abstract class Kohana_IFace extends Core_IFace {

    /**
     * Returns URL query parts array for current HTTP request
     * @param $key
     * @return array
     */
    protected function getUrlQuery($key = NULL, $default = null)
    {
        $value = Request::$current->query($key);

        return ($key AND !$value)
            ? $default
            : $value;
    }

    protected function redirect($url)
    {
        HTTP::redirect($url);
    }

}
