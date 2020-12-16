<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface AfterDispatchingInterface
 *
 * @package    BetaKiller\Url
 * @deprecated Use AfterUrlDispatchingInterface instead
 */
interface AfterDispatchingInterface
{
    /**
     * This hook executed after URL dispatching on each UrlElement in stack (on every request regardless of caching)
     * Place here the code that needs to be executed on every UrlElement "level" (no need to be the current element)
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @deprecated
     */
    public function afterDispatching(ServerRequestInterface $request): void;
}
