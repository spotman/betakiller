<?php
namespace BetaKiller\Model;

use BetaKiller\IFace\Url\UrlParameterInterface;

interface DispatchableEntityInterface extends AbstractEntityInterface, UrlParameterInterface
{
    /**
     * Entity may return instances of linked entities if it have.
     * This method is used to fetch missing entities in UrlContainer walking through links between them
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface[]
     */
    public function getLinkedEntities(): array;
}
