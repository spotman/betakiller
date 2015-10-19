<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Base IFace class for Kohana framework
 */
class Kohana_IFace extends Core_IFace {

    /**
     * Returns URL query parts array for current HTTP request
     * @param $key
     * @return array
     */
    protected function getUrlQuery($key = NULL)
    {
        return Request::$current->query($key);
    }

}
