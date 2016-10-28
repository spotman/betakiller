<?php

use BetaKiller\IFace\IFace;

class IFace_Auth_Login extends IFace {

    /**
     * @var string Default url for relocate after successful login
     */
    protected $_redirect_url = NULL;

    protected $_redirect_url_query_param = 'redirect_url';

    protected $_self_url;

    public function __construct()
    {
        $request = Request::current();

        if ( $request )
        {
            $queryString = http_build_query($request->query());
            $this->_self_url = '/'.ltrim($request->uri(), '/');

            if ($queryString) {
                $this->_self_url .= '?'.$queryString;
            }

            // Initialize redirect url
            $this->_redirect_url = urldecode($request->query($this->_redirect_url_query_param)) ?: $this->_self_url;
        }
    }

    public function render()
    {
        // If user already authorized
        if ( $this->current_user(TRUE) )
        {
            if ( $this->_redirect_url == $this->_self_url )
            {
                // Prevent infinite loops
                $this->_redirect_url = '/';
            }

            // Redirect him
            $this->redirect($this->_redirect_url);
        }

        return parent::render();
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
        return $this;
    }

    public function redirect_to_current_iface()
    {
        $url = $this->url_dispatcher()->current_iface()->url(NULL, FALSE);

        return $this->redirect_to($url);
    }

    public function get_uri()
    {
        $redirect_query = $this->_redirect_url
            ? '?'.$this->_redirect_url_query_param.'='.urlencode($this->_redirect_url)
            : NULL;

        return parent::get_uri().$redirect_query;
    }

}
