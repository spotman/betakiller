<?php
namespace BetaKiller\IFace;


use BetaKiller\IFace\Url\CustomUrlBehaviourInterface;

interface HasCustomUrlBehaviourInterface
{
    /**
     * @return CustomUrlBehaviourInterface
     */
    public function getCustomUrlBehaviour(): CustomUrlBehaviourInterface;
}
