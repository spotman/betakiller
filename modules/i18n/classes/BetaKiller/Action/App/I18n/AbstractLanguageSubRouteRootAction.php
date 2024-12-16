<?php
declare(strict_types=1);

namespace BetaKiller\Action\App\I18n;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract readonly class AbstractLanguageSubRouteRootAction extends AbstractAction
{
    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $i18n      = ServerRequestHelper::getI18n($request);
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        // Preferred language detected from cookie, accept header, etc
        $lang = $i18n->getLang();

        // Redirect
        $params = $urlHelper->createUrlContainer()->setEntity($lang);
        $url    = $urlHelper->makeCodenameUrl($this->getRedirectTargetCodename(), $params, false);

        return ResponseHelper::temporaryRedirect($url);
    }

    abstract protected function getRedirectTargetCodename(): string;
}
