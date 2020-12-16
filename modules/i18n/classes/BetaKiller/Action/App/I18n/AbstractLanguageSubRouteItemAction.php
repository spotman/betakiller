<?php
declare(strict_types=1);

namespace BetaKiller\Action\App\I18n;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Helper\RequestLanguageHelperInterface;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Url\AfterUrlDispatchingInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\RequestUserInterface;
use BetaKiller\Url\UrlElementException;
use BetaKiller\Url\UrlElementStack;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractLanguageSubRouteItemAction extends AbstractAction implements AfterUrlDispatchingInterface
{
    /**
     * @inheritDoc
     */
    public function afterDispatching(
        UrlElementStack $stack,
        UrlContainerInterface $params,
        RequestUserInterface $user,
        RequestLanguageHelperInterface $i18n
    ): void {
        /** @var \BetaKiller\Model\LanguageInterface|null $lang */
        $lang = $params->getEntityByClassName(LanguageInterface::class);

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
