<?php defined('SYSPATH') OR die('No direct script access.');

abstract class BetaKiller_IFace extends Kohana_IFace {

    final protected function process_api_response(API_Response $response)
    {
        $this->set_last_modified( $response->get_last_modified() );

        return $response->get_data();
    }

    final protected function current_user($allow_guest = FALSE)
    {
        return Env::user($allow_guest);
    }

    /**
     * @todo move to base iface
     * @return URL_Parameters
     */
    final protected function url_parameters()
    {
        return Env::url_parameters();
    }

    /**
     * @todo move to base iface
     * @return URL_Dispatcher
     */
    final protected function url_dispatcher()
    {
        return Env::url_dispatcher();
    }

    /**
     * @todo move to base iface
     * @param $codename
     * @return static
     */
    final protected function iface_factory($codename)
    {
        return self::by_codename($codename);
    }

}
