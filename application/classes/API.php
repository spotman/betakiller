<?php defined('SYSPATH') OR die('No direct script access.');


class API extends Core_API {

// uncomment when ve go through version 2
//    const VERSION = 1;

    /**
     * Helper for API::get('User')
     * Resolves autocomplete issues in IDE
     * @return API_Model_User
     */
    public static function user()
    {
        return static::get('User');
    }

}