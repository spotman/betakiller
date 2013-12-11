<?php defined('SYSPATH') OR die('No direct script access.');

abstract class IFace_Admin extends IFace {

    function __construct()
    {
        // Hide from unauthorized users
        if ( ! Env::user(TRUE) )
            throw new HTTP_Exception_404();

        parent::__construct();
    }

}