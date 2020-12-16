<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Helper\RequestLanguageHelperInterface;
use BetaKiller\Url\Container\UrlContainerInterface;

interface AfterUrlDispatchingInterface
{
    /**
     * This hook executed after URL dispatching on each UrlElement in stack (on every request regardless of caching)
     * Place here the code that needs to be executed on every UrlElement "level" (no need to be the current element)
     *
     * @param \BetaKiller\Url\UrlElementStack                   $stack
     * @param \BetaKiller\Url\Container\UrlContainerInterface   $params
     * @param \BetaKiller\Url\RequestUserInterface              $user
     * @param \BetaKiller\Helper\RequestLanguageHelperInterface $i18n
     */
    public function afterDispatching(
        UrlElementStack $stack,
        UrlContainerInterface $params,
        RequestUserInterface $user,
        RequestLanguageHelperInterface $i18n
    ): void;
}

