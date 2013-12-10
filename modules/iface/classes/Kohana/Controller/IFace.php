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

    protected function get_proxy_method()
    {
//        return parent::get_proxy_method();
        return 'render';
    }


    public function before()
    {
        $uri = $this->get_request_uri();

        // If this is default location and client requested non-slash uri, redirect client to /
        if ( $this->get_model()->is_default() AND $uri != '' )
        {
            $this->redirect('/');
        }

        parent::before();
    }

    public function get_proxy_object()
    {
        $object = $this->get_iface();

        if ( ! ($object instanceof IFace) )
            throw new Kohana_Exception('IFace controller can not serve objects which are not instance of class IFace');

        return $object;
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
     */
    protected function parse_uri($uri)
    {
        $uri_layers = $uri ? explode('/', $uri) : NULL;

//        var_dump($uri);
//        var_dump($uri_layers);
//        die();

        $provider = IFace_Provider::instance();

        // Root requested - search for default IFace
        if ( ! $uri_layers )
        {
            return $provider->get_default();
        }

        $iface_model = NULL;
        $parent_iface_model = NULL;

        // Dispatch childs
        foreach ( $uri_layers as $uri_part )
        {
            // Loop through every element and initialize it`s iface
            $iface_model = $provider->by_uri($uri_part, $parent_iface_model);

            // @TODO throw new IFace_Missing_URL($uri_part, $parent_iface_model) so we can forward user to parent iface
            // @TODO custom 404 handlers for IFaces with childs (category can show friendly message if unknown staff was requested)
            if ( ! $iface_model )
                throw new HTTP_Exception_404('Unknown url part :part', array(':part' => $uri_part));

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