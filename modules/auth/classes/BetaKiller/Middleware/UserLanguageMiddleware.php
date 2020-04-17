<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class UserLanguageMiddleware implements MiddlewareInterface
{
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
        $p = RequestProfiler::begin($request, 'User lang middleware');

        if (!ServerRequestHelper::isGuest($request)) {
            $user = ServerRequestHelper::getUser($request);
            $i18n = ServerRequestHelper::getI18n($request);

            $i18n->setLang($user->getLanguage());
        }

        RequestProfiler::end($p);

        return $handler->handle($request);
    }
}
