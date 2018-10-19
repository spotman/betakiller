<?php
declare(strict_types=1);

namespace BetaKiller\Url;

interface EntityLinkedUrlElementInterface extends UrlElementWithZoneInterface
{
    /**
     * Returns model name of the linked entity
     *
     * @return string
     */
    public function getEntityModelName(): ?string;

    /**
     * Returns entity [primary] action, applied by this URL element
     *
     * @return string
     */
    public function getEntityActionName(): ?string;
}
