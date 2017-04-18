<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper;

abstract class IFace extends \BetaKiller\IFace\KohanaIFace
{
    use Helper\IFaceTrait;

    final protected function process_api_response(\Spotman\Api\ApiMethodResponse $response)
    {
        $this->setLastModified($response->getLastModified());

        return $response->getData();
    }
}
