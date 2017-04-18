<?php
namespace BetaKiller\IFace;

/**
 * Base IFace class for Kohana framework
 * @deprecated
 * @todo Remove abstract methods from AbstractIFace and introduce RequestHelper/ResponseHelper
 */
abstract class KohanaIFace extends AbstractIFace
{
    /**
     * Returns URL query parts array for current HTTP request
     * @param string|null $key
     * @param string|null $default
     * @return array|mixed|null
     * @deprecated Use RequestHelper instead
     */
    protected function getUrlQuery($key = NULL, $default = NULL)
    {
        $value = \Request::$current->query($key);

        return ($key && !$value)
            ? $default
            : $value;
    }

    /**
     * @param $url
     * @deprecated Use ResponseHelper instead
     */
    protected function redirect($url)
    {
        \HTTP::redirect($url);
    }
}
