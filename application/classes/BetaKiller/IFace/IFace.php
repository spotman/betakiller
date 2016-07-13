<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper;

abstract class IFace extends Kohana\IFace
{
    use Helper\Base;

    final protected function process_api_response(\API_Response $response)
    {
        $this->set_last_modified($response->get_last_modified());

        return $response->get_data();
    }
}
