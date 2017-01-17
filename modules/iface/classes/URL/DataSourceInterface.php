<?php defined('SYSPATH') OR die('No direct script access.');

interface URL_DataSourceInterface
{
    /**
     * Performs search for model item where the $key property value is equal to $value
     *
     * @param string $key
     * @param string $value
     * @param URL_Parameters $parameters
     *
     * @return URL_DataSourceInterface
     */
    public function find_by_url_key($key, $value, URL_Parameters $parameters);

    /**
     * Returns default uri for index element (this used if root IFace has dynamic url behaviour)
     *
     * @return string
     */
    public function get_default_url_value();

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
     * @param string         $key
     * @param URL_Parameters $parameters
     * @param null           $limit
     *
     * @return \URL_DataSourceInterface[]
     */
    public function get_available_items_by_url_key($key, URL_Parameters $parameters, $limit = NULL);

    /**
     *
     * This method allows inheritor to preset linked model in URL parameters
     * It is executed after successful url dispatching
     *
     * @param URL_Parameters $parameters
     * @return void
     */
    public function preset_linked_models(URL_Parameters $parameters);

    /**
     * Returns custom key which may be used for storing model in URL_Parameters registry.
     * Default policy applies if NULL returned.
     *
     * @return string|null
     */
    public function get_custom_url_parameters_key();

    /**
     * Returns string identifier of current DataSource item
     *
     * @return string
     */
    public function get_url_item_id();
}
