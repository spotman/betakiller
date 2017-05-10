<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper;
use Spotman\Api\ApiMethodResponse;

abstract class IFace extends KohanaIFace
{
    use Helper\IFaceTrait;

    final protected function process_api_response(ApiMethodResponse $response)
    {
        $this->setLastModified($response->getLastModified());

        return $response->getData();
    }
}
