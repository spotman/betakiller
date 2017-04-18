<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\Utils\Registry\Base;
use Exception;

class UrlParameters extends Base
{
    public static function create()
    {
        return new static;
    }

    /**
     * @param string                 $key
     * @param UrlDataSourceInterface $object
     * @param bool|FALSE             $ignore_duplicate
     *
     * @return $this
     * @throws Exception
     */
    public function set($key, $object, $ignore_duplicate = false)
    {
        $key = $object->get_custom_url_parameters_key() ?: $key;

        parent::set($key, $object, $ignore_duplicate);

        return $this;
    }
}
