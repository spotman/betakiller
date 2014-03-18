<?php defined('SYSPATH') OR die('No direct script access.');

class Core_Controller_IFace extends Controller {

    public function action_render()
    {
        $uri = $this->get_request_uri();

        // Getting current IFace
        $iface = IFace_Provider::instance()->parse_uri($uri);

        // If this is default IFace and client requested non-slash uri, redirect client to /
        if ( $iface->is_default() AND $this->get_request_uri() != '' )
        {
            HTTP::redirect('/');
        }

        $output = $iface->render();

        $this->send_string($output);
    }

    /**
     * @return string
     */
    protected function get_request_uri()
    {
        return trim(Request::current()->detect_uri(), '/');
    }

}