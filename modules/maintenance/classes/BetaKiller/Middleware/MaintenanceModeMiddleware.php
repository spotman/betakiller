<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Exception\NotAvailableHttpException;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Service\MaintenanceModeService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MaintenanceModeMiddleware implements MiddlewareInterface
{
    /**
     * @var \BetaKiller\Service\MaintenanceModeService
     */
    private $service;

    /**
     * MaintenanceModeMiddleware constructor.
     *
     * @param \BetaKiller\Service\MaintenanceModeService $service
     */
    public function __construct(MaintenanceModeService $service)
    {
        $this->service = $service;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception\NotAvailableHttpException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = ServerRequestHelper::getUser($request);

        if (!$this->service->isEnabledFor($user)) {
            return $handler->handle($request);
        }

        $endsAt = $this->service->getEndTime();

        throw new NotAvailableHttpException($endsAt);
    }
}
