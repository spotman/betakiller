<?php defined('SYSPATH') OR die('No direct script access.');

class Kohana_Controller_IFace extends Controller_Basic {

    /**
     * @var IFace
     */
    protected $_iface = NULL;

    /**
     * @var IFace_Model
     */
    protected $_model = NULL;

    public function action_render()
    {
        // Getting current IFace
        $iface = $this->get_iface();

        if ( ! ($iface instanceof IFace) )
            throw new Kohana_Exception('IFace controller can not serve objects which are not instance of class IFace');

        // If this is default IFace and client requested non-slash uri, redirect client to /
        if ( $iface->is_default() AND $this->get_request_uri() != '' )
        {
            HTTP::redirect('/');
        }

        $view = $iface->render();

        $this->send_view($view);
    }

    /**
     * Returns interface linked to current route
     * @TODO move to Env or another factory
     * @return IFace|null
     */
    protected function get_iface()
    {
        if ( ! $this->_iface )
        {
            $this->_iface = IFace::from_model($this->get_model());
        }

        return $this->_iface;
    }

    /**
     * @return IFace_Model
     */
    protected function get_model()
    {
        if ( ! $this->_model )
        {
            $uri = $this->get_request_uri();

            // Parse uri and find IFace model
            $this->_model = $this->parse_uri($uri);
        }

        return $this->_model;
    }

    /**
     * @param string $uri
     * @return IFace_Model
     * @throws IFace_Exception_MissingURL
     */
    protected function parse_uri($uri)
    {
        $uri_parts = $uri ? explode('/', $uri) : NULL;

        $provider = IFace_Provider::instance();

        // Root requested - search for default IFace
        if ( ! $uri_parts )
        {
            return $provider->get_default();
        }

        $iface_model = NULL;
        $parent_iface_model = NULL;

        // Dispatch childs
        foreach ( $uri_parts as $uri_part )
        {
            // Loop through every uri part and initialize it`s iface
            $iface_model = $provider->by_uri($uri_part, $parent_iface_model);

            // Throw IFace_Exception_MissingURL so we can forward user to parent iface or custom 404 page
            if ( ! $iface_model )
                throw new IFace_Exception_MissingURL($uri_part, $parent_iface_model);

            $parent_iface_model = $iface_model;
        }

        // Return last model
        return $iface_model;
    }

    /**
     * @return string
     */
    protected function get_request_uri()
    {
        return trim(Request::current()->detect_uri(), '/');
    }

}