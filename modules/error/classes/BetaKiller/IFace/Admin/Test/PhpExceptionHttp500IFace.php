<?php

namespace BetaKiller\IFace\Admin\Test;

use BetaKiller\Exception\ServerErrorHttpException;
use BetaKiller\IFace\Admin\Error\AbstractErrorAdminIFace;
use Psr\Http\Message\ServerRequestInterface;

readonly class PhpExceptionHttp500IFace extends AbstractErrorAdminIFace
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
