<?php
namespace BetaKiller\Helper;

use BetaKiller\IFace\Url\UrlDataSourceInterface;
use BetaKiller\IFace\Url\UrlParametersInterface;

class AbstractUrlParametersHelper
{
    /**
     * @var \BetaKiller\IFace\Url\UrlParametersInterface
     */
    private $urlParameters;

    /**
     * ContentUrlParametersHelper constructor.
     *
     * @param \BetaKiller\IFace\Url\UrlParametersInterface $urlParameters
     */
    public function __construct(UrlParametersInterface $urlParameters)
    {
        $this->urlParameters = $urlParameters;
    }

    /**
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     */
    public function getUrlParameters()
    {
        return $this->urlParameters;
    }

    protected function get($key, UrlParametersInterface $params = null)
    {
        if (!$params) {
            $params = $this->urlParameters;
        }

        return $params->get($key);
    }

    protected function set($key, UrlDataSourceInterface $model, UrlParametersInterface $params = null, $ignoreDuplicates = false)
    {
        if (!$params) {
            $params = $this->urlParameters;
        }

        return $params->set($key, $model, $ignoreDuplicates);
    }
}
