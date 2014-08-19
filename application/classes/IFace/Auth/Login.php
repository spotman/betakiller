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

        $redirect_self_url = '/'.ltrim($request->uri(), '/');

        // Initialize redirect url
        $this->_redirect_url = $request->query($this->_redirect_url_query_param) ?: $redirect_self_url;

        // If user already authorized
        if ( Env::user(TRUE) )
        {
            if ( $this->_redirect_url == $redirect_self_url )
            {
                // Prevent infinite loops
                $this->_redirect_url = '/';
            }

            // Redirect him
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

    protected function get_uri()
    {
        $redirect_query = $this->_redirect_url
            ? '?'.$this->_redirect_url_query_param.'='.$this->_redirect_url
            : NULL;

        return parent::get_uri().$redirect_query;
    }

}
