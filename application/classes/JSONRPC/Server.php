<?php defined('SYSPATH') OR die('No direct script access.');

class JSONRPC_Server extends Kohana_JSONRPC_Server {

    protected function prepare_named_params($proxy_object, $method_name, array $args)
    {
        /** @var API_Proxy $proxy_object */

        return parent::prepare_params($proxy_object->model(), $method_name, $args);
    }

    protected function process_result($result)
    {
        /** @var API_Response $result */

        $this->_response->headers('last-modified', $result->get_last_modified()->getTimestamp());

        return $result->get_data();
    }

}
