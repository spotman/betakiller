<?php defined('SYSPATH') OR die('No direct script access.');

abstract class BetaKiller_IFace extends Kohana_IFace {

    protected function process_api_response(API_Response $response)
    {
        $this->set_last_modified( $response->get_last_modified() );

        return $response->get_data();
    }

}