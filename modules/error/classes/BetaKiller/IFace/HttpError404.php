<?php
namespace BetaKiller\IFace;

class HttpError404 extends AbstractHttpErrorIFace
{
    protected function getDefaultHttpException(): \BetaKiller\Exception\HttpExceptionInterface
    {
        return new \HTTP_Exception_404();
    }
}
