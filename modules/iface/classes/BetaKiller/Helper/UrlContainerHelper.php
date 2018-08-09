<?php
namespace BetaKiller\Helper;

use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Url\Container\ResolvingUrlContainer;
use BetaKiller\Url\Container\UrlContainer;
use BetaKiller\Url\Container\UrlContainerInterface;

class UrlContainerHelper
{
    /**
     * @var \BetaKiller\Url\Container\UrlContainerInterface
     */
    private $urlContainer;

    /**
     * ContentUrlContainerHelper constructor.
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlParameters
     */
    public function __construct(UrlContainerInterface $urlParameters)
    {
        $this->urlContainer = $urlParameters;
    }

    /**
     * @return \BetaKiller\Url\Container\UrlContainerInterface
     */
    public function createSimple(): UrlContainerInterface
    {
        return UrlContainer::create();
    }

    public function createResolving(): ResolvingUrlContainer
    {
        return new ResolvingUrlContainer;
    }

    /**
     * @return \BetaKiller\Url\Container\UrlContainerInterface
     */
    public function getCurrentUrlParameters(): UrlContainerInterface
    {
        return $this->urlContainer;
    }

    public function getEntity($key, UrlContainerInterface $params = null)
    {
        if (!$params) {
            $params = $this->urlContainer;
        }

        return $params->getEntity($key);
    }

    public function getEntityByClassName($className, UrlContainerInterface $params = null)
    {
        if (!$params) {
            $params = $this->urlContainer;
        }

        return $params->getEntityByClassName($className);
    }

    public function setEntity(DispatchableEntityInterface $model, UrlContainerInterface $params = null): UrlContainerInterface
    {
        if (!$params) {
            $params = $this->urlContainer;
        }

        return $params->setEntity($model, true);
    }

    public function getQueryPart(string $name, ?bool $required = null)
    {
        return $this->urlContainer->getQueryPart($name, $required);
    }
}
