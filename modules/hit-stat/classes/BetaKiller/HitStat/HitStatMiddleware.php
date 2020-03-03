<?php
declare(strict_types=1);

namespace BetaKiller\HitStat;

use BetaKiller\Command\HitStatStoreCommand;
use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Exception\RedirectException;
use BetaKiller\Exception\SeeOtherHttpException;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\MessageBus\CommandBusInterface;
use BetaKiller\Middleware\RequestUuidMiddleware;
use BetaKiller\Model\HitMarkerInterface;
use BetaKiller\Service\HitService;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class HitStatMiddleware implements MiddlewareInterface
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Service\HitService
     */
    private $service;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Psr\Http\Message\UriFactoryInterface
     */
    private $uriFactory;

    /**
     * @var \BetaKiller\MessageBus\CommandBusInterface
     */
    private $commandBus;

    /**
     * HitStatMiddleware constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface         $appEnv
     * @param \BetaKiller\Service\HitService             $service
     * @param \Psr\Http\Message\UriFactoryInterface      $uriFactory
     * @param \BetaKiller\MessageBus\CommandBusInterface $commandBus
     * @param \Psr\Log\LoggerInterface                   $logger
     */
    public function __construct(
        AppEnvInterface $appEnv,
        HitService $service,
        UriFactoryInterface $uriFactory,
        CommandBusInterface $commandBus,
        LoggerInterface $logger
    ) {
        $this->appEnv     = $appEnv;
        $this->service    = $service;
        $this->logger     = $logger;
        $this->uriFactory = $uriFactory;
        $this->commandBus = $commandBus;
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Remove UTM markers to simplify further UrlElement processing
        $request = ServerRequestHelper::removeQueryParams($request, HitMarkerInterface::UTM_QUERY_KEYS);

        // Skip calls like "cache warmup" from CLI mode
        if ($this->appEnv->isInternalWebServer()) {
            return $handler->handle($request);
        }

        try {
            $p = RequestProfiler::begin($request, 'Hit stat (total processing)');

            $this->processHit($request);
        } catch (RedirectException $e) {
            // Re-throw redirect
            throw $e;
        } catch (Throwable $e) {
            $this->logException($this->logger, $e);
        }

        RequestProfiler::end($p);

        // Forward call
        return $handler->handle($request);
    }

    private function processHit(ServerRequestInterface $request): void
    {
        $sourceUrl = ServerRequestHelper::getHttpReferrer($request);
        $targetUri = $request->getUri();
        $ip        = ServerRequestHelper::getIpAddress($request);

        try {
            $sourceUri = $sourceUrl ? $this->uriFactory->createUri($sourceUrl) : null;
        } catch (InvalidArgumentException $e) {
            // Malformed source => ignore it
            $sourceUri = !$e;
        }

        $p1 = RequestProfiler::begin($request, 'Hit stat: detect source page');

        // Find source page
        $sourcePage = $sourceUri ? $this->service->getPageByFullUrl($sourceUri) : null;

        RequestProfiler::end($p1);

        // Skip ignored pages and domains
        if ($sourcePage && $sourcePage->isIgnored()) {
            return;
        }

        $p2 = RequestProfiler::begin($request, 'Hit stat: detect target page');

        // Search for target URL and create if not exists
        $targetPage = $this->service->getPageByFullUrl($targetUri);

        RequestProfiler::end($p2);

        // Skip ignored pages and domains
        if ($targetPage->isIgnored()) {
            return;
        }

        // If target page is missing and redirect is defined => redirect
        if ($targetPage->isMissing()) {
            $redirect = $targetPage->getRedirect();

            if ($redirect) {
                throw new SeeOtherHttpException($redirect->getUrl());
            }
        }

        // Detect marker
        $marker = $this->service->getMarkerFromRequest($request);

        $session   = ServerRequestHelper::getSession($request);
        $requestId = RequestUuidMiddleware::getUuid($request);

        // This is required for saving first user hit during registration
        if (!HitStatSessionHelper::hasFirstHitUuid($session)) {
            HitStatSessionHelper::setFirstHitUuid($session, $requestId);
        }

        $p3 = RequestProfiler::begin($request, 'Hit stat: enqueue command');

        $user = ServerRequestHelper::getUser($request);
        $uuid = RequestUuidMiddleware::getUuid($request);

        // Call command
        $this->commandBus->enqueue(
            new HitStatStoreCommand($uuid, $user, $ip, $sourcePage, $targetPage, $marker)
        );

        RequestProfiler::end($p3);
    }
}
