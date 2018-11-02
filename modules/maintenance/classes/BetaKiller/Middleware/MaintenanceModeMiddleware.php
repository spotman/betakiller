<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Exception\NotAvailableHttpException;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Service\MaintenanceModeDebugBarDataCollector;
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
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * MaintenanceModeMiddleware constructor.
     *
     * @param \BetaKiller\Service\MaintenanceModeService $service
     * @param \BetaKiller\Helper\AppEnvInterface         $appEnv
     */
    public function __construct(MaintenanceModeService $service, AppEnvInterface $appEnv)
    {
        $this->service = $service;
        $this->appEnv  = $appEnv;
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
        // Skip check during cache:warmup
        if ($this->appEnv->isInternalWebServer()) {
            return $handler->handle($request);
        }

        $user = ServerRequestHelper::getUser($request);

        $debugBar = ServerRequestHelper::getDebugBar($request);

        if ($debugBar) {
            $model = $this->service->getModel();
            $debugBar->addCollector(new MaintenanceModeDebugBarDataCollector($model));
        }

        if (!$this->service->isEnabled() || !$this->service->isDisplayedFor($user)) {
            return $handler->handle($request);
        }

        $endsAt = $this->service->getEndTime();

        throw new NotAvailableHttpException($endsAt);
    }
}
