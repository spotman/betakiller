<?php defined('SYSPATH') OR die('No direct script access.');

interface URL_DataSource {

    /**
     * Performs search for model item where the $key property value is equal to $value
     *
     * @param string $key
     * @param string $value
     * @param URL_Parameters $parameters
     * @return URL_DataSource
     */
    public function find_by_url_key($key, $value, URL_Parameters $parameters);

    /**
     * Returns value of the $key property
     * @param string $key
     * @return string
     */
    public function get_url_key_value($key);

}