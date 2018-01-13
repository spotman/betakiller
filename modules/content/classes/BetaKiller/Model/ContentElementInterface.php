<?php
namespace BetaKiller\Model;

interface ContentElementInterface extends AbstractEntityInterface, EntityItemRelatedInterface
{
    /**
     * Returns true if content element has all required info
     *
     * @return bool
     */
    public function isValid(): bool;
}
