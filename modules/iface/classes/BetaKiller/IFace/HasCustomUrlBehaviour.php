<?php
namespace BetaKiller\IFace;


use BetaKiller\IFace\Url\CustomUrlBehaviourInterface;

interface HasCustomUrlBehaviour
{
    /**
     * @return CustomUrlBehaviourInterface
     */
    public function get_custom_url_behaviour();
}
