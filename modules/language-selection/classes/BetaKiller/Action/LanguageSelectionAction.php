<?php
declare(strict_types=1);

namespace BetaKiller\Action;


use BetaKiller\Helper\I18nHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\Middleware\I18nMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LanguageSelectionAction extends AbstractAction
{
    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $i18NFacade;

    /**
     * @var \Psr\Http\Server\RequestHandlerInterface
     */
    private $requestHandler;

    /**
     * @param \Psr\Http\Server\RequestHandlerInterface $requestHandler
     * @param \BetaKiller\I18n\I18nFacade              $i18NFacade
     */
    public function __construct(RequestHandlerInterface $requestHandler, I18nFacade $i18NFacade)
    {
        $this->i18NFacade     = $i18NFacade;
        $this->requestHandler = $requestHandler;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $langCode = ServerRequestHelper::getQueryPart($request, 'lang_code', true);
        $langCode = strtolower(trim($langCode));
        $this->setLangCookie($request, $langCode);

        return ResponseHelper::successJson();
    }


    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param string                                   $langCode
     *
     * @return \BetaKiller\Action\LanguageSelectionAction
     * @throws \BetaKiller\Exception
     */
    public function setLangCookie(ServerRequestInterface $request, string $langCode): self
    {
        $i18n = new I18nHelper($this->i18NFacade);
        $i18n->setLang($langCode);

        $response = $this->requestHandler->handle($request->withAttribute(I18nHelper::class, $i18n));

        $cookieName   = I18nMiddleware::COOKIE_NAME;
        $dateInterval = I18nMiddleware::COOKIE_NAME;
        ResponseHelper::setCookie($response, $cookieName, $langCode, new \DateInterval($dateInterval));

        return $this;
    }
}
