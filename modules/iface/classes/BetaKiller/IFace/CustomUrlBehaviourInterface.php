<?php
namespace BetaKiller\IFace;

interface CustomUrlBehaviourInterface
{
    public function process_custom_url_behaviour(UrlPathIterator $it, \URL_Parameters $params = NULL);
}
