<?php defined('SYSPATH') OR die('No direct script access.');

class Auth_Exception_Inactive extends Auth_Exception {

    /**
     * @return string
     */
    protected function get_default_message()
    {
        return __('Your account was switched off');
    }

}
