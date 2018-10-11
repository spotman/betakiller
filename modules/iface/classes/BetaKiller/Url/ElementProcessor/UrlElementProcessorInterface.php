<?php
namespace BetaKiller\Url\ElementProcessor;

use BetaKiller\Url\UrlElementInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * URL element processor like IFace, WebHook and etc
 */
interface UrlElementProcessorInterface
{
    /**
     * Execute processing on URL element
     *
     * @param \BetaKiller\Url\UrlElementInterface      $model
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(
        UrlElementInterface $model,
        ServerRequestInterface $request
    ): ResponseInterface;
}
