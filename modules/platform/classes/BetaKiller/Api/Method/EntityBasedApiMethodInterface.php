<?php
namespace BetaKiller\Api\Method;

use BetaKiller\Model\AbstractEntityInterface;
use Spotman\Api\ApiMethodInterface;
use Spotman\Defence\ArgumentsInterface;

interface EntityBasedApiMethodInterface extends ApiMethodInterface
{
    /**
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     *
     * @return \BetaKiller\Model\AbstractEntityInterface
     */
    public function getEntity(ArgumentsInterface $arguments): AbstractEntityInterface;
}
