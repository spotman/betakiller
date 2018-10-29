<?php
declare(strict_types=1);

namespace BetaKiller\Action;

use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\I18nHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\Middleware\I18nMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LanguageSelectionAction extends AbstractAction
{
    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $i18NFacade;

    /**
     * @param \BetaKiller\I18n\I18nFacade $i18NFacade
     */
    public function __construct(I18nFacade $i18NFacade)
    {
        $this->i18NFacade = $i18NFacade;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $postData = ServerRequestHelper::getPost($request);

        $langCode = $postData['lang_code'] ?? null;
        $langCode = strtolower(trim($langCode));

        if (!$langCode) {
            throw new BadRequestHttpException('Not found language code.');
        }

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
        // todo how to setup cookie?
        $i18n = new I18nHelper($this->i18NFacade);
        $i18n->setLang($langCode);

        //$response = $handler->handle($request->withAttribute(I18nHelper::class, $i18n));

        $cookieName   = I18nMiddleware::COOKIE_NAME;
        $dateInterval = I18nMiddleware::DATE_INTERVAL;
        ResponseHelper::setCookie($response, $cookieName, $langCode, new \DateInterval($dateInterval));

        return $this;
    }
}
