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
     * Response controller
     *
     * @var \Response
     */
    protected $response;

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

    /**
     * Setting response controller
     *
     * @param \Response $response
     *
     * @return $this
     */
    public function setResponse(\Response $response): UrlElementProcessorInterface
    {
        $this->response = $response;

        return $this;
    }
}
