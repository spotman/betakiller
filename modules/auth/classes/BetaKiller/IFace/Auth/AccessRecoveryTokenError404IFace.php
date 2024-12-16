<?php

declare(strict_types=1);

namespace BetaKiller\IFace\Auth;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\IFace\AbstractIFace;
use Psr\Http\Message\ServerRequestInterface;

readonly class AccessRecoveryTokenError404IFace extends AbstractIFace
{
    /**
     * AccessRecoveryTokenError404IFace constructor.
     *
     * @param \BetaKiller\I18n\I18nFacade $i18n
     */
    public function __construct(private I18nFacade $i18n)
    {
    }

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $lang = !ServerRequestHelper::isGuest($request)
            ? ServerRequestHelper::getUser($request)->getLanguage()
            : $this->i18n->getPrimaryLanguage();

        $params = $urlHelper->createUrlContainer()->setEntity($lang);

        return [
            'request_url' => $urlHelper->makeCodenameUrl(AccessRecoveryRequestIFace::codename(), $params),
        ];
    }
}
