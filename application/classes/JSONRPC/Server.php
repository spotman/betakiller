<?php
use Spotman\Api\ApiModelResponse;
use Spotman\Api\ApiProxy;

class JSONRPC_Server extends Kohana_JSONRPC_Server {

    /**
     * Use real model object for preparing arguments
     *
     * @param ApiProxy $proxy_object
     * @param string   $method_name
     * @param array    $args
     * @return array
     */
    protected function prepare_named_params($proxy_object, $method_name, array $args)
    {
        return parent::prepare_params($proxy_object->getModel(), $method_name, $args);
    }

    /**
     * Convert ApiModelResponse to array and register last_modified
     *
     * @param ApiModelResponse $result
     * @return mixed
     */
    protected function process_result($result)
    {
        $this->_response->last_modified( $result->getLastModified() );

        return $result->getData();
    }
}
