<?php
namespace BetaKiller\IFace\Auth;

use BetaKiller\Exception\FoundHttpException;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\AbstractIFace;
use Psr\Http\Message\ServerRequestInterface;

class Login extends AbstractIFace
{
    public const URL = '/login/';

    public function getData(ServerRequestInterface $request): array
    {
        // If user already authorized
        if (!ServerRequestHelper::isGuest($request)) {
            // Redirect him to index (this is a fallback if an authorized user visited /login )
            throw new FoundHttpException('/');
        }

        return [];
    }
}
