<?php
declare(strict_types=1);

namespace BetaKiller\HitStat;

use BetaKiller\Dev\Profiler;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\Hit;
use BetaKiller\Model\HitInterface;
use BetaKiller\Model\HitMarkerInterface;
use BetaKiller\Model\HitPage;
use BetaKiller\Repository\HitLinkRepository;
use BetaKiller\Repository\HitPageRepository;
use BetaKiller\Repository\HitRepository;
use BetaKiller\Service\HitService;
use BetaKiller\Service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class HitStatMiddleware implements MiddlewareInterface
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Service\HitService
     */
    private $service;

    /**
     * @var \BetaKiller\Repository\HitRepository
     */
    private $hitRepo;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \BetaKiller\Repository\HitPageRepository
     */
    private $pageRepo;

    /**
     * @var \BetaKiller\Repository\HitLinkRepository
     */
    private $linkRepo;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Service\UserService
     */
    private $userService;

    /**
     * HitStatMiddleware constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface       $appEnv
     * @param \BetaKiller\Service\HitService           $service
     * @param \BetaKiller\Repository\HitRepository     $hitRepo
     * @param \BetaKiller\Repository\HitPageRepository $pageRepo
     * @param \BetaKiller\Repository\HitLinkRepository $linkRepo
     * @param \Psr\Log\LoggerInterface                 $logger
     */
    public function __construct(
        AppEnvInterface $appEnv,
        HitService $service,
        HitRepository $hitRepo,
        HitPageRepository $pageRepo,
        HitLinkRepository $linkRepo,
        UserService $userService,
        LoggerInterface $logger
    ) {
        $this->appEnv   = $appEnv;
        $this->service  = $service;
        $this->hitRepo  = $hitRepo;
        $this->pageRepo = $pageRepo;
        $this->linkRepo = $linkRepo;
        $this->logger   = $logger;
        $this->userService = $userService;
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

        $p = Profiler::begin($request, 'Hit stat processing');

        try {
            $hit = $this->registerHit($request);

            if ($hit) {
                // Inject Hit into Request
                $request = HitStatRequestHelper::setHit($request, $hit);

                $target = $hit->getTarget();

                // If target page is missing and redirect is defined => return redirect
                if ($target->isMissing()) {
                    $redirect = $target->getRedirect();

                    if ($redirect) {
                        return ResponseHelper::redirect($redirect->getUrl());
                    }
                }

                $this->injectHitInSession($hit, $request);
            }
        } catch (\Throwable $e) {
            $this->logException($this->logger, $e);
        }

        Profiler::end($p);

        // Forward call in case of exception
        return $handler->handle($request);
    }

    private function injectHitInSession(HitInterface $hit, ServerRequestInterface $request): void
    {
        $session = ServerRequestHelper::getSession($request);

        // Store ref hit if it is not exist
        if (!HitStatSessionHelper::hasFirstHit($session)) {
            HitStatSessionHelper::setFirstHit($session, $hit);
        }
    }

    private function registerHit(ServerRequestInterface $request): ?HitInterface
    {
        $sourceUrl = ServerRequestHelper::getHttpReferrer($request);
        $targetUrl = (string)$request->getUri();
        $ip        = ServerRequestHelper::getIpAddress($request);

        // Find source page
        $sourcePage = $sourceUrl ? $this->service->getPageByFullUrl($sourceUrl) : null;

        // Skip ignored pages and domains
        if ($sourcePage && $sourcePage->isIgnored()) {
            return null;
        }

        $now = new \DateTimeImmutable;

        // Search for target URL and create if not exists
        $targetPage = $this->service->getPageByFullUrl($targetUrl);

        // Increment hit counter for target URL
        $targetPage
            ->incrementHits()
            ->setLastSeenAt($now);

        $this->pageRepo->save($targetPage);

        // Process source page if exists
        if ($sourcePage) {
            $sourcePage->setLastSeenAt($now);

            // If source page is missing, mark it as existing
            if ($sourcePage->isMissing()) {
                $sourcePage->markAsOk();
            }

            $this->pageRepo->save($sourcePage);

            // Register link
            $this->processLink($sourcePage, $targetPage);
        }

        // Detect marker
        $marker = $this->service->getMarkerFromRequest($request);

        // Create new Hit object with source/target pages, marker, ip and other info
        $hit = new Hit;

        $hit
            ->setTargetPage($targetPage)
            ->setIP($ip)
            ->setTimestamp($now);

        if ($sourcePage) {
            $hit->setSourcePage($sourcePage);
        }

        if ($marker) {
            $hit->setTargetMarker($marker);
        }

        if (ServerRequestHelper::hasUser($request)) {
            $user = ServerRequestHelper::getUser($request);

            // Ignore hits of admin users
            if ($this->userService->isAdmin($user)) {
                return null;
            }

            $hit->bindToUser($user);
        }

        $this->hitRepo->save($hit);

        return $hit;
    }

    private function processLink(HitPage $source, HitPage $target): void
    {
        $link = $this->service->getLinkBySourceAndTarget($source, $target);

        // Increment link click counter
        $link->incrementClicks();

        $now = new \DateTimeImmutable;

        if (!$link->getFirstSeenAt()) {
            $link->setFirstSeenAt($now);
        }

        $link->setLastSeenAt($now);

        $this->linkRepo->save($link);
    }
}