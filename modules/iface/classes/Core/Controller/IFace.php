<?php defined('SYSPATH') OR die('No direct script access.');

class Core_Controller_IFace extends Controller
{
    public function action_render()
    {
        $uri = $this->get_request_uri();

        $dispatcher = \BetaKiller\DI\Container::instance()->get(\URL_Dispatcher::class);

        // Getting current IFace
        $iface = $dispatcher->parse_uri($uri);

        $has_trailing_slash = (substr($uri, -1, 1) == '/');

        $is_trailing_slash_enabled = $iface->is_trailing_slash_enabled();

        if ($has_trailing_slash AND !$is_trailing_slash_enabled)
        {
            $this->redirect(rtrim($uri, '/'), 301); // Permanent redirect
        }
        elseif (!$has_trailing_slash AND $is_trailing_slash_enabled)
        {
            $this->redirect($uri.'/', 301); // Permanent redirect
        }

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
        return Request::current()->detect_uri();
    }
}
