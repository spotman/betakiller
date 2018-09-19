<?php
namespace BetaKiller\Url\ElementProcessor;

use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlElementInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * URL element processor like IFace, WebHook and etc
 */
interface UrlElementProcessorInterface
{
    /**
     * Execute processing on URL element
     *
     * @param \BetaKiller\Url\UrlElementInterface             $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlContainer
     * @param \Psr\Http\Message\ServerRequestInterface        $request
     * @param \Response                                       $response
     */
    public function process(
        UrlElementInterface $model,
        UrlContainerInterface $urlContainer,
        ServerRequestInterface $request,
        \Response $response
    ): void;
}
