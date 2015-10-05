<?php defined('SYSPATH') OR die('No direct script access.');


class ORM extends Util_ORM implements API_Response_Item, URL_DataSource /* , DataSource_Interface */ {

    /**
     * Default implementation for ORM objects
     * Override this method in child classes
     *
     * @return array
     */
    public function get_api_response_data()
    {
        return $this->as_array();
    }

    /**
     * Default implementation for ORM objects
     * Override this method in child classes
     *
     * @return DateTime|NULL
     */
    public function get_last_modified()
    {
        // Empty by default
        return NULL;
    }

    /**
     * Performs search for model item where the $key property value is equal to $value
     *
     * @param string $key
     * @param string $value
     * @param URL_Parameters $parameters
     * @return URL_DataSource|NULL
     */
    public function find_by_url_key($key, $value, URL_Parameters $parameters)
    {
        // Additional filtering for non-pk keys
        if ( $key != $this->primary_key() )
        {
            $this->custom_find_by_url_filter($parameters);
        }

        $model = $this->where($this->object_column($key), '=', $value)->find();

        return $model->loaded() ? $model : NULL;
    }

    /**
     * @param URL_Parameters $parameters
     */
    protected function custom_find_by_url_filter(URL_Parameters $parameters)
    {
        // Empty by default
    }

    /**
     * Returns value of the $key property
     * @param string $key
     * @return string
     */
    public function get_url_key_value($key)
    {
        return (string) $this->get($key);
    }

    /**
     * Returns list of available items (model records) by $key property
     *
     * @param string $key
     * @param URL_Parameters $parameters
     * @return URL_DataSource[]
     */
    public function get_available_items_by_url_key($key, URL_Parameters $parameters)
    {
        // Additional filtering for non-pk keys
        if ( $key != $this->primary_key() )
        {
            $this->custom_find_by_url_filter($parameters);
        }

        $key_column = $this->object_column($key);

        $models = $this->where($key_column, 'IS NOT', NULL)->group_by($key_column)->find_all();

        return $models->count() ? $models->as_array() : NULL;
    }

    public function get_validation_exception_errors(ORM_Validation_Exception $e)
    {
        return $e->errors('models');
    }

    /**
     * Enables the query to be cached for a specified amount of time.
     * Cache lifetime is taken from config file called "clt.php" with structure <table name> => <seconds>
     *
     * @param integer $lifetime number of seconds to cache
     * @return  $this
     * @uses    Kohana::$cache_life
     */
    public function cached($lifetime = NULL)
    {
        // Do nothing if not in production
        if ( ! Kohana::in_production() )
            return $this;

        if ( ! $lifetime )
            $lifetime = Kohana::config('clt')->get($this->table_name(), 60);

        return parent::cached($lifetime);
    }

}
