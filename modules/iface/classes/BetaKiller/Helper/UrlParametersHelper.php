<?php
namespace BetaKiller\Helper;

use BetaKiller\IFace\Url\UrlParameters;
use BetaKiller\IFace\Url\UrlParametersInterface;
use BetaKiller\Model\DispatchableEntityInterface;

class UrlParametersHelper
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
    public function createEmpty()
    {
        return UrlParameters::create();
    }

    /**
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     */
    public function getCurrentUrlParameters()
    {
        return $this->urlParameters;
    }

    protected function getEntity($key, UrlParametersInterface $params = null)
    {
        if (!$params) {
            $params = $this->urlParameters;
        }

        return $params->getEntity($key);
    }

    protected function getEntityByClassName($className, UrlParametersInterface $params = null)
    {
        if (!$params) {
            $params = $this->urlParameters;
        }

        return $params->getEntityByClassName($className);
    }

    protected function setEntity(DispatchableEntityInterface $model, UrlParametersInterface $params = null, $ignoreDuplicates = false)
    {
        if (!$params) {
            $params = $this->urlParameters;
        }

        return $params->setEntity($model, $ignoreDuplicates);
    }
}
