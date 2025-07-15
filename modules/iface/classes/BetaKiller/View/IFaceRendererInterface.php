<?php

declare(strict_types=1);

namespace BetaKiller\View;

use BetaKiller\IFace\IFaceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface IFaceRendererInterface
{
    /**
     * Render provided IFace instance
     *
     * @param \BetaKiller\IFace\IFaceInterface         $iface
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function render(IFaceInterface $iface, ServerRequestInterface $request): ResponseInterface;
}
