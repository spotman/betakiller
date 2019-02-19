<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Session\DatabaseSessionStorage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionMiddleware extends \Zend\Expressive\Session\SessionMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $csp = ServerRequestHelper::getCsp($request);

        // This cookie is used by WAMP client as auth id
        $csp->protectedCookie(DatabaseSessionStorage::COOKIE_NAME, $csp::COOKIE_REMOVE);

        return parent::process($request, $handler);
    }
}
