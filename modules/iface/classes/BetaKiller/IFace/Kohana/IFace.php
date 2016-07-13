<?php
namespace BetaKiller\IFace\Kohana;

use BetaKiller\IFace\Core;

/**
 * Base IFace class for Kohana framework
 */
abstract class IFace extends Core\IFace
{
    /**
     * Returns URL query parts array for current HTTP request
     * @param $key
     * @return array
     */
    protected function getUrlQuery($key = NULL, $default = NULL)
    {
        $value = \Request::$current->query($key);

        return ($key AND !$value)
            ? $default
            : $value;
    }

    protected function redirect($url)
    {
        \HTTP::redirect($url);
    }
}
