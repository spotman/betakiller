<?php defined('SYSPATH') or die('No direct access allowed.');

class Device extends Kohana_Device {

    use Util_Factory_Simple;

    public function is_portable($http_headers = NULL, $http_headers = NULL)
    {
        return $this->is_mobile($http_headers, $http_headers) OR $this->is_tablet($http_headers, $http_headers);
    }

}