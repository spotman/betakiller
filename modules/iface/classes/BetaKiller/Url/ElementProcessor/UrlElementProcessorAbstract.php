<?php
namespace BetaKiller\Url\ElementProcessor;

abstract class UrlElementProcessorAbstract implements UrlElementProcessorInterface
{
    /**
     * Request controller
     *
     * @var \Request
     */
    protected $request;

    /**
     * Setting request controller
     *
     * @param \Request $request
     *
     * @return $this
     */
    public function setRequest(\Request $request): UrlElementProcessorInterface
    {
        $this->request = $request;

        return $this;
    }
}
