<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class Auth_Exception extends HTTP_Exception_403 {

//    protected function get_view_path($file = NULL)
//    {
//        $type = str_replace(__CLASS__.'_', '', get_class($this));
//
//        $view_path = 'auth/'. strtolower( $ty );
//
//        return parent::get_view_path($view_path);
//    }

    /**
     * @return string
     */
    protected function get_default_message()
    {
        return __('Authentication failed');
    }

}