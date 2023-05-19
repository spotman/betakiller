<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Exception\NotAvailableHttpException;
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
    private MaintenanceModeService $service;

    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * MaintenanceModeMiddleware constructor.
     *
     * @param \BetaKiller\Service\MaintenanceModeService $service
     * @param \BetaKiller\Env\AppEnvInterface            $appEnv
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

        $p = RequestProfiler::begin($request, 'Maintenance mode middleware');

//        if (DebugServerRequestHelper::hasDebugBar($request)) {
//            $model = $this->service->getModel();
//
//            DebugServerRequestHelper::getDebugBar($request)
//                ->addCollector(new MaintenanceModeDebugBarDataCollector($model));
//        }

        if (!$this->service->isEnabled()) {
            RequestProfiler::end($p);

            return $handler->handle($request);
        }

        $endsAt = $this->service->getEndTime();

        RequestProfiler::end($p);

        throw new NotAvailableHttpException($endsAt);
    }
}
