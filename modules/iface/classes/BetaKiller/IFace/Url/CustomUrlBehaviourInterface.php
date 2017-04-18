<?php
namespace BetaKiller\IFace\Url;

interface CustomUrlBehaviourInterface
{
    public function processCustomUrlBehaviour(UrlPathIterator $it, UrlParameters $params = NULL);
}
