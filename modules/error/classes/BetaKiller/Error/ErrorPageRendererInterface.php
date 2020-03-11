<?php
declare(strict_types=1);

namespace BetaKiller\Error;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ErrorPageRendererInterface
{
    public function render(ServerRequestInterface $request, \Throwable $e): ResponseInterface;
}
