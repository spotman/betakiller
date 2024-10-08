<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Service\SitemapService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SitemapRequestHandler implements RequestHandlerInterface
{
    /**
     * @var \BetaKiller\Service\SitemapService
     */
    private $service;

    /**
     * SitemapRequestHandler constructor.
     *
     * @param \BetaKiller\Service\SitemapService $service
     */
    public function __construct(SitemapService $service)
    {
        $this->service = $service;
    }

    /**
     * Handle the request and return a response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Service\ServiceException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->service->generate()->serve();
    }
}
