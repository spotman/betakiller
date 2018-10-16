<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Auth\AuthFacade;
use BetaKiller\Dev\Profiler;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserMiddleware implements MiddlewareInterface
{
    /**
     * @var \BetaKiller\Auth\AuthFacade
     */
    private $auth;

    /**
     * UserMiddleware constructor.
     *
     * @param \BetaKiller\Auth\AuthFacade $auth
     */
    public function __construct(AuthFacade $auth)
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
        $pid = Profiler::begin($request, 'User detection');

        $session  = ServerRequestHelper::getSession($request);
        $user = $this->auth->getSessionUser($session);

        Profiler::end($pid);

        return $handler->handle($request->withAttribute(UserInterface::class, $user));
    }
}
