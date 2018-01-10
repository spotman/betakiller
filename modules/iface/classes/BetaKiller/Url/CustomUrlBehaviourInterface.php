<?php
namespace BetaKiller\Url;

interface CustomUrlBehaviourInterface
{
    public function processCustomUrlBehaviour(UrlPathIterator $it, UrlContainerInterface $params = null);
}
