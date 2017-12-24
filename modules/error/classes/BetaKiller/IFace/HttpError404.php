<?php
namespace BetaKiller\IFace;

class HttpError404 extends AbstractHttpErrorIFace
{
    protected function getDefaultHttpException(): \HTTP_Exception
    {
        return new \HTTP_Exception_404();
    }
}
