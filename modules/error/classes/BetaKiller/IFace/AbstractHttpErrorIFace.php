<?php

namespace BetaKiller\IFace;

use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

abstract readonly class AbstractHttpErrorIFace extends AbstractIFace
{
    public static function injectException(ServerRequestInterface $request, Throwable $e): ServerRequestInterface
    {
        return $request->withAttribute(self::class, $e);
    }

    public static function fetchException(ServerRequestInterface $request): ?Throwable
    {
        return $request->getAttribute(self::class);
    }

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
//        $redirectData = [
//            AuthWidget::REDIRECT_KEY => ServerRequestHelper::getUrl($request),
//        ];

        $user = !ServerRequestHelper::isGuest($request) ? ServerRequestHelper::getUser($request) : null;

        $e = self::fetchException($request);

        return [
            'url'     => $request->getUri()->getPath(),
            'email'   => $user?->getEmail(),
            'message' => $e?->getMessage(),
            'trace'   => $e?->getTraceAsString(),
//            'login_url' => LoginIFace::URL.'?'.http_build_query($redirectData),
//            'is_guest'  => ServerRequestHelper::isGuest($request),
        ];
    }
}
