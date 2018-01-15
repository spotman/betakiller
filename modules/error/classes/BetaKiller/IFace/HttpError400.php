<?php
namespace BetaKiller\IFace;

use BetaKiller\Exception\HttpExceptionInterface;

class HttpError400 extends AbstractHttpErrorIFace
{
    /**
     * @return \BetaKiller\Exception\HttpExceptionInterface
     */
    protected function getDefaultHttpException(): HttpExceptionInterface
    {
        return new \HTTP_Exception_400();
    }
}
