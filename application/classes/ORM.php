<?php defined('SYSPATH') OR die('No direct script access.');


class ORM extends Util_ORM implements API_Response_Item /* , DataSource_Interface */ {

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
     * @return NULL
     */
    public function get_last_modified()
    {
        // Empty by default
        return NULL;
    }

}