<?php
namespace BetaKiller\IFace\Auth;

use BetaKiller\IFace\AbstractIFace;

class PasswordReset extends AbstractIFace
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData(): array
    {
        return [];
    }
}
