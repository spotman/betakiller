<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper;

abstract class IFace extends Kohana\IFace
{
    use Helper\IFaceTrait;

    final protected function process_api_response(\API_Response $response)
    {
        $this->setLastModified($response->get_last_modified());

        return $response->get_data();
    }
}
