<?php
namespace BetaKiller\Url\ElementProcessor;

use \BetaKiller\Url\UrlElementInterface;
use \BetaKiller\Url\Container\UrlContainerInterface;

/**
 * URL element processor like IFace, WabHok and etc
 */
interface UrlElementProcessorInterface
{
    /**
     * Execute processing on URL element
     *
     * @param \BetaKiller\Url\UrlElementInterface             $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlContainer
     * @param \Response|null                                  $response [optional]
     * @param \Request|null                                   $request  [optional]
     */
    public function process(
        UrlElementInterface $model,
        UrlContainerInterface $urlContainer,
        ?\Response $response = null,
        ?\Request $request = null
    ): void;
}
