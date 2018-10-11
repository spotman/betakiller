<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper\ServerRequestHelper;
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
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @uses \BetaKiller\IFace\Auth\Login
     */
    public function getData(ServerRequestInterface $request): array
    {
        $user      = ServerRequestHelper::getUser($request);
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $login = $urlHelper->getUrlElementByCodename('Auth_Login');

        return [
            'login_url' => $urlHelper->makeUrl($login),
            'is_guest'  => $user->isGuest(),
        ];
    }
}
