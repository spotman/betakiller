<?php
namespace BetaKiller\Api\Method;

use Spotman\Api\ApiMethodInterface;

interface EntityBasedApiMethodInterface extends ApiMethodInterface
{
    /**
     * @return \BetaKiller\Model\AbstractEntityInterface
     */
    public function getEntity();
}
