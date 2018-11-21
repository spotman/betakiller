<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use Psr\Http\Message\ServerRequestInterface;

interface BeforeProcessingInterface
{
    /**
     * This hook executed before UrlElement processing (on every request regardless of caching)
     * Place here code that needs to be executed on every UrlElement request (increment views counter, etc)
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function beforeProcessing(ServerRequestInterface $request): void;
}
