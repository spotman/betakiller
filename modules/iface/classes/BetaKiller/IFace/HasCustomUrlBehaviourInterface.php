<?php
namespace BetaKiller\IFace;


use BetaKiller\Url\CustomUrlBehaviourInterface;

interface HasCustomUrlBehaviourInterface
{
    /**
     * @return CustomUrlBehaviourInterface
     */
    public function getCustomUrlBehaviour(): CustomUrlBehaviourInterface;
}
