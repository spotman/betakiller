<?php
declare(strict_types=1);

namespace BetaKiller\HitStat;

use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Exception\HttpExceptionExpectedInterface;
use BetaKiller\Exception\SeeOtherHttpException;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\Hit;
use BetaKiller\Model\HitInterface;
use BetaKiller\Repository\HitRepositoryInterface;
use BetaKiller\Service\HitService;
use InvalidArgumentException;
use Mezzio\Session\SessionIdentifierAwareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class HitStatMiddleware implements MiddlewareInterface
{
    /**
     * @var \BetaKiller\Service\HitService
     */
    private HitService $service;

    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var \Psr\Http\Message\UriFactoryInterface
     */
    private UriFactoryInterface $uriFactory;

    /**
     * @var \BetaKiller\Repository\HitRepositoryInterface
     */
    private HitRepositoryInterface $hitRepo;

    /**
     * HitStatMiddleware constructor.
     *
     * @param \BetaKiller\Env\AppEnvInterface               $appEnv
     * @param \BetaKiller\Service\HitService                $service
     * @param \Psr\Http\Message\UriFactoryInterface         $uriFactory
     * @param \BetaKiller\Repository\HitRepositoryInterface $hitRepo
     * @param \Psr\Log\LoggerInterface                      $logger
     */
    public function __construct(
        AppEnvInterface        $appEnv,
        HitService             $service,
        UriFactoryInterface    $uriFactory,
        HitRepositoryInterface $hitRepo,
        LoggerInterface        $logger
    ) {
        $this->appEnv     = $appEnv;
        $this->service    = $service;
        $this->logger     = $logger;
        $this->uriFactory = $uriFactory;
        $this->hitRepo    = $hitRepo;
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
        $i = RequestProfiler::begin($request, 'Hit stat (init)');

        // Skip calls like "cache warmup" from CLI mode
        if ($this->appEnv->isInternalWebServer()) {
            RequestProfiler::end($i);

            return $handler->handle($request);
        }

        RequestProfiler::end($i);

        try {
            $p = RequestProfiler::begin($request, 'Hit stat (processing)');

            $hit = $this->processHit($request);

            if ($hit) {
                $request = HitStatRequestHelper::withHit($request, $hit);
            }
        } catch (HttpExceptionExpectedInterface $e) {
            // Re-throw redirect
            throw $e;
        } catch (Throwable $e) {
            LoggerHelper::logRequestException($this->logger, $e, $request);
        } finally {
            RequestProfiler::end($p);
        }

        // Forward call
        return $handler->handle($request);
    }

    private function processHit(ServerRequestInterface $request): ?HitInterface
    {
        $ip        = ServerRequestHelper::getIpAddress($request);
        $userAgent = ServerRequestHelper::getUserAgent($request);
        $sourceUrl = ServerRequestHelper::getHttpReferrer($request);
        $targetUri = $request->getUri();

        // Prevent stupid spammers and bots
        if (!$userAgent) {
            return null;
        }

        try {
            // Prevent wrong URLs
            if ($sourceUrl && \mb_strpos($sourceUrl, '/') === false) {
                throw new InvalidArgumentException;
            }

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
            return null;
        }

        $p2 = RequestProfiler::begin($request, 'Hit stat: detect target page');

        // Search for target URL and create if not exists
        $targetPage = $this->service->getPageByFullUrl($targetUri);

        RequestProfiler::end($p2);

        // Skip ignored pages and domains
        if ($targetPage->isIgnored()) {
            return null;
        }

        // If target page is missing and redirect is defined => redirect
        if ($targetPage->isMissing()) {
            $redirect = $targetPage->getRedirect();

            $redirectException = $redirect ? new SeeOtherHttpException($redirect->getUrl()) : null;

            // Log redirect instead of processing and skip this stat hit
            if ($redirectException && $this->appEnv->inDevelopmentMode()) {
                LoggerHelper::logRequestException(
                    $this->logger,
                    $redirectException,
                    $request
                );

                return null;
            }

            if ($redirectException) {
                throw $redirectException;
            }
        }

        $session   = ServerRequestHelper::getSession($request);
        $requestId = ServerRequestHelper::getRequestUuid($request);
        $params    = ServerRequestHelper::getUrlContainer($request);

        if (!$requestId) {
            throw new \LogicException('Request UUID is missing');
        }

        // Detect marker
        $marker = $this->service->getMarkerFromUrlContainer($params);

        $p3 = RequestProfiler::begin($request, 'Hit stat: store');

        if (!$session instanceof SessionIdentifierAwareInterface) {
            throw new \LogicException(
                sprintf('Session object must implement %s', SessionIdentifierAwareInterface::class)
            );
        }

        // Create new Hit object with source/target pages, marker, ip and other info
        $hit = new Hit;

        $hit->setCreatedAt(new \DateTimeImmutable());

        $hit
            ->setIP($ip)
            ->setUuid($requestId)
            ->setSessionToken($session->getId())
            ->setTargetPage($targetPage);

        if ($sourcePage) {
            $hit->setSourcePage($sourcePage);
        }

        if ($marker) {
            $hit->setTargetMarker($marker);
        }

        $this->hitRepo->save($hit);

        RequestProfiler::end($p3);

        return $hit;
    }
}
