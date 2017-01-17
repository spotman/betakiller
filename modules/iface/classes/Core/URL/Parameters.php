<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\Utils\Registry\Base;

abstract class Core_URL_Parameters extends Base
{
    use \BetaKiller\Utils\Instance\Simple;

    /**
     * @param string                  $key
     * @param URL_DataSourceInterface $object
     * @param bool|FALSE              $ignore_duplicate
     *
     * @return $this
     * @throws Exception
     */
    public function set($key, $object, $ignore_duplicate = FALSE)
    {
        $key = $object->get_custom_url_parameters_key() ?: $key;

        parent::set($key, $object, $ignore_duplicate);

        return $this;
    }
}
