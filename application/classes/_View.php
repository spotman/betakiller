<?php defined('SYSPATH') OR die('No direct script access.');

class _View extends Kohana_View
{
    /**
     * Helper for getting value which was previously set
     * @param $key
     * @return mixed
     */
    public function & get($key)
    {
        // return $this->$key;
        return $this->__get($key);
    }
}
