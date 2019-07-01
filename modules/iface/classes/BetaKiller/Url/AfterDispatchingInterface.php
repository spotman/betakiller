<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Model\UserInterface;
use BetaKiller\Url\Container\UrlContainerInterface;

interface AfterDispatchingInterface
{
    /**
     * This hook executed after URL dispatching on each UrlElement in stack (on every request regardless of caching)
     * Place here the code that needs to be executed on every UrlElement "level" (no need to be the current element)
     *
     * @param \BetaKiller\Url\UrlElementStack                 $stack
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param \BetaKiller\Model\UserInterface                 $user
     */
    public function afterDispatching(UrlElementStack $stack, UrlContainerInterface $params, UserInterface $user): void;
}
