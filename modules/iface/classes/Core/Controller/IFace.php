<?php defined('SYSPATH') OR die('No direct script access.');

class Core_Controller_IFace extends Controller {

    public function action_render()
    {
        $uri = $this->get_request_uri();

        // Getting current IFace
        $iface = URL_Dispatcher::instance()->parse_uri($uri);

        // If this is default IFace and client requested non-slash uri, redirect client to /
        if ( $iface->is_default() AND $this->get_request_uri() != '' )
        {
            $this->redirect('/');
        }

        $output = $iface->render();

        $last_modified = $iface->get_last_modified() ?: $iface->get_default_last_modified();
        $expires_interval = $iface->get_expires_interval() ?: $iface->get_default_expires_interval();

        $expires = (new DateTime())->add($expires_interval);

        $this->last_modified($last_modified);
        $this->expires($expires);

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
