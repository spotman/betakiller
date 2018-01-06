<?php
namespace BetaKiller\IFace;

class HttpError500 extends AbstractHttpErrorIFace
{
    protected function getDefaultHttpException(): \BetaKiller\Exception\HttpExceptionInterface
    {
        return new \HTTP_Exception_500();
    }
}
