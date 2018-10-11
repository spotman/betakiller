<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Helper\I18nHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class I18nMiddleware implements MiddlewareInterface
{
    private const COOKIE_NAME = 'lang';

    /**
     * @var \BetaKiller\Helper\I18nHelper
     */
    private $i18n;

    /**
     * @var \BetaKiller\Helper\ResponseHelper
     */
    private $responseHelper;

    /**
     * I18nMiddleware constructor.
     *
     * @param \BetaKiller\Helper\I18nHelper     $i18n
     * @param \BetaKiller\Helper\ResponseHelper $responseHelper
     */
    public function __construct(
        I18nHelper $i18n,
        ResponseHelper $responseHelper
    ) {
        $this->i18n           = $i18n;
        $this->responseHelper = $responseHelper;
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
        $lang = $this->detectLang($request);

        $this->i18n->setLang($lang);

        $response = $handler->handle($request->withAttribute(I18nHelper::class, $this->i18n));

        $expiresIn = new \DateInterval('P14D');

        return ResponseHelper::setCookie($response, self::COOKIE_NAME, $lang, $expiresIn);
    }

    private function detectLang(ServerRequestInterface $request): string
    {
        // Check authorized user language
        $userLang = $this->detectUserLang($request);

        if ($userLang) {
            return $userLang;
        }

        // Detect language via cookie and content negotiation
        $httpLang = $this->detectHttpLang($request);

        if ($httpLang) {
            return $httpLang;
        }

        // App default language as fallback
        return $this->i18n->getAppDefaultLanguage();
    }

    private function detectUserLang(ServerRequestInterface $request): ?string
    {
        $user = ServerRequestHelper::getUser($request);

        if ($user->isGuest()) {
            return null;
        }

        return $user->getLanguageName();
    }

    private function detectHttpLang(ServerRequestInterface $request): string
    {
        $lang = ServerRequestHelper::getCookie($request, self::COOKIE_NAME);

        // Cookie lang has priority
        if ($lang) {
            return $lang;
        }

        return ServerRequestHelper::getPreferredLanguage($request);
    }
}
