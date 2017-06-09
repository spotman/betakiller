<?php defined('SYSPATH') OR die('No direct script access.');

class HTTP_Exception extends Kohana_HTTP_Exception {

    /**
     * Returns nice exception response in non-dev modes
     *
     * @return Response
     */
    public function get_response()
    {
        return parent::_handler($this);
    }
}
