<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Dev\Profiler;
use BetaKiller\Helper\I18nHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\I18n\I18nFacade;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class I18nMiddleware implements MiddlewareInterface
{
    public const COOKIE_NAME = 'lang';
    public const DATE_INTERVAL = 'P14D';

    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $facade;

    /**
     * I18nMiddleware constructor.
     *
     * @param \BetaKiller\I18n\I18nFacade $facade
     */
    public function __construct(I18nFacade $facade)
    {
        $this->facade = $facade;
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
    //получение url: url helper передать ему url элемент
    //получение авторизованного пользователя: server request helper has user
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $pid = Profiler::begin($request, 'Language detection');

        $lang = $this->detectLang($request);

        $i18n = new I18nHelper($this->facade);
        $i18n->setLang($lang);

        Profiler::end($pid);

        $response = $handler->handle($request->withAttribute(I18nHelper::class, $i18n));

        return ResponseHelper::setCookie($response, self::COOKIE_NAME, $lang, new \DateInterval(self::DATE_INTERVAL));
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
        return $this->facade->getDefaultLanguage();
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
