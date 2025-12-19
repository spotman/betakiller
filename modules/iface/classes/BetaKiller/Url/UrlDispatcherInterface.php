<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Helper\RequestLanguageHelperInterface;
use BetaKiller\Model\RequestUserInterface;
use BetaKiller\Url\Container\UrlContainerInterface;

interface UrlDispatcherInterface
{
    /**
     * @param string                                            $uri
     * @param \BetaKiller\Url\UrlElementStack                   $stack
     * @param \BetaKiller\Url\Container\UrlContainerInterface   $params
     * @param \BetaKiller\Model\RequestUserInterface            $user
     * @param \BetaKiller\Helper\RequestLanguageHelperInterface $i18n
     *
     * @return void
     * @throws \BetaKiller\Url\MissingUrlElementException
     */
    public function process(
        string $uri,
        UrlElementStack $stack,
        UrlContainerInterface $params,
        RequestUserInterface $user,
        RequestLanguageHelperInterface $i18n
    ): void;
}
