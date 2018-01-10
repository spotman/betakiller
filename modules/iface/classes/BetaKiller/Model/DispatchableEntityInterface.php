<?php
namespace BetaKiller\Model;

use BetaKiller\Url\UrlParameterInterface;

interface DispatchableEntityInterface extends AbstractEntityInterface, UrlParameterInterface
{
    /**
     * Returns value of the $key property
     *
     * @param string $key
     *
     * @return string
     */
    public function getUrlKeyValue(string $key): string;

    /**
     * Entity may return instances of linked entities if it have.
     * This method is used to fetch missing entities in UrlContainer walking through links between them
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface[]
     */
    public function getLinkedEntities(): array;
}
