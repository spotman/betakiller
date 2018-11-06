<?php
namespace BetaKiller\Api\Method;

use BetaKiller\Model\AbstractEntityInterface;
use Spotman\Api\ApiMethodInterface;
use Spotman\Api\ArgumentsInterface;

interface EntityBasedApiMethodInterface extends ApiMethodInterface
{
    /**
     * @param \Spotman\Api\ArgumentsInterface $arguments
     *
     * @return \BetaKiller\Model\AbstractEntityInterface
     */
    public function getEntity(ArgumentsInterface $arguments): AbstractEntityInterface;
}
