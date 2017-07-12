<?php
namespace BetaKiller\Helper;

use BetaKiller\IFace\Url\UrlContainer;
use BetaKiller\IFace\Url\UrlContainerInterface;
use BetaKiller\Model\DispatchableEntityInterface;

class UrlContainerHelper
{
    /**
     * @var \BetaKiller\IFace\Url\UrlContainerInterface
     */
    private $urlContainer;

    /**
     * ContentUrlContainerHelper constructor.
     *
     * @param \BetaKiller\IFace\Url\UrlContainerInterface $urlParameters
     */
    public function __construct(UrlContainerInterface $urlParameters)
    {
        $this->urlContainer = $urlParameters;
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
        return $this->urlContainer;
    }

    protected function getEntity($key, UrlContainerInterface $params = null)
    {
        if (!$params) {
            $params = $this->urlContainer;
        }

        return $params->getEntity($key);
    }

    protected function getEntityByClassName($className, UrlContainerInterface $params = null)
    {
        if (!$params) {
            $params = $this->urlContainer;
        }

        return $params->getEntityByClassName($className);
    }

    protected function setEntity(DispatchableEntityInterface $model, UrlContainerInterface $params = null): UrlContainerInterface
    {
        if (!$params) {
            $params = $this->urlContainer;
        }

        return $params->setParameter($model, true);
    }

    public function getQueryPart(string $name, ?bool $required = null)
    {
        return $this->urlContainer->getQueryPart($name, $required);
    }
}
