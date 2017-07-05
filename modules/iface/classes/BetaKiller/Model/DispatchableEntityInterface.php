<?php
namespace BetaKiller\Model;

use BetaKiller\IFace\Url\UrlParameterInterface;
use BetaKiller\IFace\Url\UrlContainerInterface;

interface DispatchableEntityInterface extends AbstractEntityInterface, UrlParameterInterface
{
    /**
     *
     * This method allows inheritor to preset linked model in URL parameters
     * It is executed after successful url dispatching
     *
     * @param \BetaKiller\IFace\Url\UrlContainerInterface $parameters
     *
     * @return void
     * @deprecated Implement dynamic linking based on scheme (relations between entities)
     */
    public function presetLinkedEntities(UrlContainerInterface $parameters): void;
}
