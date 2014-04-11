<?php defined('SYSPATH') OR die('No direct script access.');

class IFace_Auth_Login extends IFace {

    /**
     * @var string Default url for relocate after successful login
     */
    protected $_redirect_url = NULL;

    protected $_redirect_url_query_param = 'redirect_url';

    public function __construct()
    {
        $request = Request::current();

        // Initialize redirect url
        $this->_redirect_url = $request->query($this->_redirect_url_query_param) ?: '/'.$request->uri();

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

    public function redirect_to($redirect_url)
    {
        $this->_redirect_url = $redirect_url;
    }

    public function url()
    {
        $redirect_query = $this->_redirect_url
            ? '?'.$this->_redirect_url_query_param.'='.$this->_redirect_url
            : NULL;

        return parent::url().$redirect_query;
    }

}