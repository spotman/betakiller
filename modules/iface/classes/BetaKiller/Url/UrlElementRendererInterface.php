<?php

declare(strict_types=1);

namespace BetaKiller\Url;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface UrlElementRendererInterface
{
    /**
     * @param \BetaKiller\Url\UrlElementInterface      $urlElement
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception\FoundHttpException
     */
    public function render(UrlElementInterface $urlElement, ServerRequestInterface $request): ResponseInterface;
}
