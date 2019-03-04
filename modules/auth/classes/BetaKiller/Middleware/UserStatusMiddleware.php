<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Auth\BlockedIFace;
use BetaKiller\IFace\Auth\SuspendedIFace;
use BetaKiller\Model\UserStatus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserStatusMiddleware implements MiddlewareInterface
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
        $elementCodename = $this->detectTargetCodename($request);

        // Forward call
        if (!$elementCodename) {
            return $handler->handle($request);
        }

        $helper  = ServerRequestHelper::getUrlHelper($request);
        $element = $helper->getUrlElementByCodename($elementCodename);

        // Prevent cycling redirects and allow activation action
        if ($helper->inStack($element)) {
            return $handler->handle($request);
        }

        $url = $helper->makeUrl($element);

        return ResponseHelper::redirect($url);
    }

    private function detectTargetCodename(ServerRequestInterface $request): ?string
    {
        if (ServerRequestHelper::isGuest($request)) {
            return null;
        }

        $status = ServerRequestHelper::getUser($request)->getStatus();

        switch ($status->getCodename()) {
            case UserStatus::STATUS_BLOCKED:
                return BlockedIFace::codename();

            case UserStatus::STATUS_SUSPENDED:
                return SuspendedIFace::codename();

            default:
                return null;
        }
    }
}
