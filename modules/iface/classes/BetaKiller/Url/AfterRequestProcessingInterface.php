<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use Psr\Http\Message\ServerRequestInterface;

interface AfterRequestProcessingInterface
{
    /**
     * This hook executed after real UrlElement processing only (on every request if output was not cached)
     * Place here the code that needs to be executed only after real UrlElement processing (collect performance stat, etc)
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function afterProcessing(ServerRequestInterface $request): void;
}
