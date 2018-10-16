<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Auth\Login;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractHttpErrorIFace extends AbstractIFace
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
        return [
            'login_url' => Login::URL,
            'is_guest'  => ServerRequestHelper::isGuest($request),
        ];
    }
}
