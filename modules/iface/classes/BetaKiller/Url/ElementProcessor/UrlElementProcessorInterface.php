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
     * @param \BetaKiller\Url\UrlElementInterface                  $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $urlContainer [optional]
     * @param \Response|null                                       $response [optional]
     */
    public function process(
        UrlElementInterface $model,
        ?UrlContainerInterface  $urlContainer = null,
        ?\Response  $response = null
    ): void;

    /**
     * @param \Request $request
     *
     * @return \BetaKiller\Url\ElementProcessor\UrlElementProcessorInterface
     */
    public function setRequest(\Request $request): UrlElementProcessorInterface;
}
