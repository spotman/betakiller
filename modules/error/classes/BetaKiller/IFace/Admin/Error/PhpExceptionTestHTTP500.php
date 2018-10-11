<?php
namespace BetaKiller\IFace\Admin\Error;

use BetaKiller\Exception\ServerErrorHttpException;
use Psr\Http\Message\ServerRequestInterface;

class PhpExceptionTestHTTP500 extends ErrorAdminBase
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        throw new ServerErrorHttpException();
    }
}
