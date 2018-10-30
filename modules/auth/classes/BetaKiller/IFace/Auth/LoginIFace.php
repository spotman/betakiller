<?php
namespace BetaKiller\IFace\Auth;

use BetaKiller\Exception\FoundHttpException;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\AbstractIFace;
use Psr\Http\Message\ServerRequestInterface;

class LoginIFace extends AbstractIFace
{
    public const URL = '/login/';

    /**
     * This hook executed before IFace processing (on every request regardless of caching)
     * Place here code that needs to be executed on every IFace request (increment views counter, etc)
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function before(ServerRequestInterface $request): void
    {
        // If user already authorized
        if (!ServerRequestHelper::isGuest($request)) {
            // Redirect him to index (this is a fallback if an authorized user visited /login )
            throw new FoundHttpException('/');
        }
    }

    public function getData(ServerRequestInterface $request): array
    {
        return [];
    }
}
