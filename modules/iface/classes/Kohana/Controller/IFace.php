<?php defined('SYSPATH') OR die('No direct script access.');

class Kohana_Controller_IFace extends Controller {

    public function action_render()
    {
        // Getting current IFace
        $iface_model = $this->parse_uri();

        $iface = IFace::factory($iface_model);

        // If this is default IFace and client requested non-slash uri, redirect client to /
        if ( $iface->is_default() AND $this->get_request_uri() != '' )
        {
            HTTP::redirect('/');
        }

        $view = $iface->render();

        $this->send_view($view);
    }

    /**
     * @return IFace_Model
     * @throws IFace_Exception_MissingURL
     */
    protected function parse_uri()
    {
        $uri = $this->get_request_uri();

        $uri_parts = $uri ? explode('/', $uri) : NULL;

        $provider = IFace_Provider::instance();

        // Root requested - search for default IFace
        if ( ! $uri_parts )
        {
            return $provider->get_default();
        }

        $iface_model = NULL;
        $iface_instance = NULL;
        $parent_iface_model = NULL;

        // Dispatch childs
        foreach ( $uri_parts as $uri_part )
        {
            // Loop through every uri part and initialize it`s iface
            $iface_model = $provider->by_uri($uri_part, $parent_iface_model);

            // Throw IFace_Exception_MissingURL so we can forward user to parent iface or custom 404 page
            if ( ! $iface_model )
                throw new IFace_Exception_MissingURL($uri_part, $parent_iface_model);

            // Creating instance of IFace (for future usage)
            IFace::factory($iface_model);

            $parent_iface_model = $iface_model;
        }

        // Return last IFace
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