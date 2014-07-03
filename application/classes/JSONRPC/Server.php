<?php defined('SYSPATH') OR die('No direct script access.');

class JSONRPC_Server extends Kohana_JSONRPC_Server {

    /**
     * Use real model object for preparing arguments
     * TODO ???
     *
     * @param API_Proxy $proxy_object
     * @param string $method_name
     * @param array $args
     * @return array
     */
    protected function prepare_named_params($proxy_object, $method_name, array $args)
    {
        return parent::prepare_params($proxy_object->model(), $method_name, $args);
    }

    /**
     * Convert API_Response to array and register last_modified
     *
     * @param API_Response $result
     * @return mixed
     */
    protected function process_result($result)
    {
        $this->_response->last_modified( $result->get_last_modified()->getTimestamp() );

        return $result->get_data();
    }

}
