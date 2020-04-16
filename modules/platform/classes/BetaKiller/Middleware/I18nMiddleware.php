<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Helper\CookieHelper;
use BetaKiller\Helper\I18nHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\I18n\I18nFacade;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class I18nMiddleware implements MiddlewareInterface
{
    public const COOKIE_NAME          = 'lang';
    public const COOKIE_DATE_INTERVAL = 'P14D';

    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $facade;

    /**
     * @var \BetaKiller\Helper\CookieHelper
     */
    private $cookies;

    /**
     * I18nMiddleware constructor.
     *
     * @param \BetaKiller\I18n\I18nFacade     $facade
     * @param \BetaKiller\Helper\CookieHelper $cookieHelper
     */
    public function __construct(I18nFacade $facade, CookieHelper $cookieHelper)
    {
        $this->facade  = $facade;
        $this->cookies = $cookieHelper;
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
        $pid = RequestProfiler::begin($request, 'Language detection');

        $langIsoCode = $this->detectLangIsoCode($request);

        $lang = $langIsoCode
            ? $this->facade->getLanguageByIsoCode($langIsoCode)
            : $this->facade->getPrimaryLanguage(); // App default language as fallback

        $i18n = new I18nHelper($this->facade);
        $i18n->setLang($lang);

        RequestProfiler::end($pid);

        $response = $handler->handle($request->withAttribute(I18nHelper::class, $i18n));

        // Allow other middleware to change language via I18nHelper::setLang()
        return $this->cookies->set(
            $response,
            self::COOKIE_NAME,
            $i18n->getLang()->getIsoCode(),
            new \DateInterval(self::COOKIE_DATE_INTERVAL)
        );
    }

    private function detectLangIsoCode(ServerRequestInterface $request): ?string
    {
        // Detect language via cookie and content negotiation
        return $this->detectHttpLang($request);
    }

    private function detectHttpLang(ServerRequestInterface $request): string
    {
        $lang = $this->cookies->get($request, self::COOKIE_NAME);

        // Cookie lang has priority
        if ($lang) {
            return $lang;
        }

        return ServerRequestHelper::getPreferredLanguage($request);
    }
}
