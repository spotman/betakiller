<?php defined('SYSPATH') OR die('No direct script access.');

class JSONRPC_Exception extends Kohana_JSONRPC_Exception {

    protected function get_default_message()
    {
        // Allow displaying of original messages
        return $this->getMessage();
    }

}
