<?php
namespace BetaKiller\IFace;

class HttpError403 extends AbstractHttpErrorIFace
{
    protected function getDefaultHttpException(): \HTTP_Exception
    {
        return new \HTTP_Exception_403();
    }
}
