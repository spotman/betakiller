<?php

use BetaKiller\Exception\DefaultExceptionBehaviourTrait;
use BetaKiller\Exception\HttpExceptionInterface;

class HTTP_Exception extends Kohana_HTTP_Exception implements HttpExceptionInterface
{
    use DefaultExceptionBehaviourTrait;

    /**
     * Returns nice exception response in non-dev modes
     *
     * @return Response
     */
    public function get_response()
    {
        return parent::_handler($this);
    }
}
