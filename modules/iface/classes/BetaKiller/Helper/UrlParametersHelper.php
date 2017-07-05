<?php
namespace BetaKiller\Helper;

use BetaKiller\IFace\Url\UrlContainer;
use BetaKiller\IFace\Url\UrlContainerInterface;
use BetaKiller\Model\DispatchableEntityInterface;

class UrlParametersHelper
{
    /**
     * @var \BetaKiller\IFace\Url\UrlContainerInterface
     */
    private $urlParameters;

    /**
     * ContentUrlParametersHelper constructor.
     *
     * @param \BetaKiller\IFace\Url\UrlContainerInterface $urlParameters
     */
    public function __construct(UrlContainerInterface $urlParameters)
    {
        $this->urlParameters = $urlParameters;
    }

    /**
     * @return \BetaKiller\IFace\Url\UrlContainerInterface
     */
    public function createEmpty(): UrlContainerInterface
    {
        return UrlContainer::create();
    }

    /**
     * @return \BetaKiller\IFace\Url\UrlContainerInterface
     */
    public function getCurrentUrlParameters(): UrlContainerInterface
    {
        return $this->urlParameters;
    }

    protected function getEntity($key, UrlContainerInterface $params = null)
    {
        if (!$params) {
            $params = $this->urlParameters;
        }

        return $params->getEntity($key);
    }

    protected function getEntityByClassName($className, UrlContainerInterface $params = null)
    {
        if (!$params) {
            $params = $this->urlParameters;
        }

        return $params->getEntityByClassName($className);
    }

    protected function setEntity(DispatchableEntityInterface $model, UrlContainerInterface $params = null): UrlContainerInterface
    {
        if (!$params) {
            $params = $this->urlParameters;
        }

        return $params->setParameter($model, true);
    }

    public function getQueryPart(string $name, ?bool $required = null)
    {
        return $this->urlParameters->getQueryPart($name, $required);
    }
}
