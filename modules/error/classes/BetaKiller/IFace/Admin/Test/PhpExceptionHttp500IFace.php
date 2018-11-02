<?php
namespace BetaKiller\IFace\Admin\Test;

use BetaKiller\Exception\ServerErrorHttpException;
use BetaKiller\IFace\Admin\Error\ErrorAdminBase;
use Psr\Http\Message\ServerRequestInterface;

class PhpExceptionHttp500IFace extends ErrorAdminBase
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