<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Url\Container\UrlContainerInterface;

interface UrlDispatcherInterface
{
    /**
     * @param string                                          $uri
     * @param \BetaKiller\Url\UrlElementStack                 $stack
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return void
     * @throws \BetaKiller\Url\MissingUrlElementException
     */
    public function process(string $uri, UrlElementStack $stack, UrlContainerInterface $params): void;
}
