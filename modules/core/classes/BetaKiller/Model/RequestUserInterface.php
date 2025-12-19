<?php

declare(strict_types=1);

namespace BetaKiller\Model;

/**
 * Interface RequestUserInterface
 * Marker interface for User model (processed during HTTP request dispatch)
 */
interface RequestUserInterface extends AbstractEntityInterface
{
    /**
     * Returns true if current user is a guest
     *
     * @return bool
     */
    public function isGuest(): bool;
}
