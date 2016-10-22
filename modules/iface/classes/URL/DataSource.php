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
     *
     * @param string $key
     * @return string
     */
    public function get_url_key_value($key);

    /**
     * Returns list of available items (model records) by $key property
     *
     * @param string $key
     * @param URL_Parameters $parameters
     * @return URL_DataSource[]
     */
    public function get_available_items_by_url_key($key, URL_Parameters $parameters);

    /**
     *
     * This method allows inheritor to preset linked model in URL parameters
     * It is executed after successful url dispatching
     *
     * @param URL_Parameters $parameters
     * @return void
     */
    public function preset_linked_models(URL_Parameters $parameters);
}
