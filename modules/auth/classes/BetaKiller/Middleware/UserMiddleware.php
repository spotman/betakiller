<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Service\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserMiddleware implements MiddlewareInterface
{
    /**
     * @var \BetaKiller\Service\AuthService
     */
    private $auth;

    /**
     * UserMiddleware constructor.
     *
     * @param \BetaKiller\Service\AuthService $auth
     */
    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = ServerRequestHelper::getSession($request);
        $user    = $this->auth->getSessionUser($session);

        return $handler->handle(ServerRequestHelper::setUser($request, $user));
    }
}
