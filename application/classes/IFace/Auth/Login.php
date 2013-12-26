<?php defined('SYSPATH') OR die('No direct script access.');

class IFace_Auth_Login extends IFace {

    /**
     * @var string Default url for relocate after successful login
     */
    protected $_redirect_url = "/";

    public function __construct()
    {
        // If user already authorized
        if ( Env::user(TRUE) )
        {
            // Redirect him to index page
            HTTP::redirect($this->_redirect_url);
        }
    }

    public function get_data()
    {
        return array(
            'redirect_url'  => $this->_redirect_url
        );
    }

    public function redirect_to_current_page()
    {
        $this->_redirect_url = '/'.Request::current()->uri();
    }

}