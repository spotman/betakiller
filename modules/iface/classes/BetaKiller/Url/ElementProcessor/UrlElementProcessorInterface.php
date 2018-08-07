<?php
namespace BetaKiller\Url\ElementProcessor;

/**
 * URL element processor like IFace, WabHok and etc
 */
interface UrlElementProcessorInterface
{
    /**
     * Execute processing on URL element
     */
    public function process(): void;

    /**
     * @param \Request $request
     *
     * @return \BetaKiller\Url\ElementProcessor\UrlElementProcessorInterface
     */
    public function setRequest(\Request $request): self;

    /**
     * @param \Response $response
     *
     * @return \BetaKiller\Url\ElementProcessor\UrlElementProcessorInterface
     */
    public function setResponse(\Response $response): self;
}
