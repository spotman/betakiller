<?php
namespace BetaKiller\IFace\Url;

interface UrlDataSourceInterface
{
    /**
     * Performs search for model item where the $key property value is equal to $value
     *
     * @param string        $key
     * @param string        $value
     * @param UrlParameters $parameters
     *
     * @return UrlDataSourceInterface
     */
    public function find_by_url_key($key, $value, UrlParameters $parameters);

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
     *
     * @return string
     */
    public function get_url_key_value($key);

    /**
     * Returns list of available items (model records) by $key property
     *
     * @param string        $key
     * @param UrlParameters $parameters
     * @param null          $limit
     *
     * @return UrlDataSourceInterface[]
     */
    public function get_available_items_by_url_key($key, UrlParameters $parameters, $limit = null);

    /**
     *
     * This method allows inheritor to preset linked model in URL parameters
     * It is executed after successful url dispatching
     *
     * @param UrlParameters $parameters
     *
     * @return void
     */
    public function preset_linked_models(UrlParameters $parameters);

    /**
     * Returns custom key which may be used for storing model in UrlParameters registry.
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
