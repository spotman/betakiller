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
     * @return URL_DataSource|NULL
     */
    public function find_by_url_key($key, $value)
    {
        $model = $this->where($this->object_name().'.'.$key, '=', $value)->find();
        return $model->loaded() ? $model : NULL;
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

}