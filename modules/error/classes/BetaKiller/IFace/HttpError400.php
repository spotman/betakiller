<?php
namespace BetaKiller\IFace;

class HttpError400 extends AbstractHttpErrorIFace
{
    protected function getDefaultHttpException(): \HTTP_Exception
    {
        return new \HTTP_Exception_400();
    }
}
