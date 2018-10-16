<?php
namespace BetaKiller\IFace\Auth;

use BetaKiller\IFace\AbstractIFace;
use Psr\Http\Message\ServerRequestInterface;

class PasswordReset extends AbstractIFace
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        return [];
    }
}
