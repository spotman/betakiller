<?php
declare(strict_types=1);

namespace BetaKiller\Action\App\I18n;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Url\AfterDispatchingInterface;
use BetaKiller\Url\UrlElementException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractLanguageSubRouteAction extends AbstractAction implements AfterDispatchingInterface
{
    /**
     * @inheritDoc
     */
    public function afterDispatching(ServerRequestInterface $request): void
    {
        $i18n = ServerRequestHelper::getI18n($request);

        /** @var \BetaKiller\Model\LanguageInterface $lang */
        $lang = ServerRequestHelper::getEntity($request, LanguageInterface::class);

        if (!$lang) {
            throw new UrlElementException('Can not process subroute coz of missing Language UrlParameter');
        }

        // Override i18n language with parsed model
        $i18n->setLang($lang);
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $url = ServerRequestHelper::getUrlHelper($request)->makeCodenameUrl($this->getRedirectTargetCodename());

        return ResponseHelper::temporaryRedirect($url);
    }

    abstract protected function getRedirectTargetCodename(): string;
}
