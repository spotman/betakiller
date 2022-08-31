<?php
declare(strict_types=1);

namespace BetaKiller\Task\HitStat;

use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Model\HitInterface;
use BetaKiller\Model\HitPageInterface;
use BetaKiller\Repository\HitLinkRepository;
use BetaKiller\Repository\HitPageRepositoryInterface;
use BetaKiller\Repository\HitRepositoryInterface;
use BetaKiller\Repository\UserSessionRepository;
use BetaKiller\Service\HitService;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

class ProcessHits extends AbstractTask
{
    public const MISSING_TARGETS = 'admin/hit-stat/missing-targets';
    public const NEW_SOURCES     = 'admin/hit-stat/new-sources';

    /**
     * @var \BetaKiller\Repository\HitRepositoryInterface
     */
    private HitRepositoryInterface $hitsRepository;

    /**
     * @var \BetaKiller\Model\Hit[]
     */
    private $processed = [];

    /**
     * @var \BetaKiller\Model\HitPage[]
     */
    private $newMissingTargets = [];

    /**
     * @var \BetaKiller\Model\HitPage[]
     */
    private $newMissingSources = [];

    /**
     * @var \BetaKiller\Model\HitPage[]
     */
    private $newSources = [];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notification;

    /**
     * @var \BetaKiller\Repository\UserSessionRepository
     */
    private $sessionRepo;

    /**
     * @var \BetaKiller\Repository\HitPageRepositoryInterface
     */
    private $pageRepo;

    /**
     * @var \BetaKiller\Repository\HitLinkRepository
     */
    private $linkRepo;

    /**
     * @var \BetaKiller\Service\HitService
     */
    private $service;

    /**
     * ProcessHits constructor.
     *
     * @param \BetaKiller\Repository\HitRepositoryInterface     $hitsRepository
     * @param \BetaKiller\Repository\HitPageRepositoryInterface $pageRepo
     * @param \BetaKiller\Repository\HitLinkRepository          $linkRepo
     * @param \BetaKiller\Repository\UserSessionRepository      $sessionRepo
     * @param \BetaKiller\Service\HitService                    $service
     * @param \BetaKiller\Helper\NotificationHelper             $notification
     * @param \Psr\Log\LoggerInterface                          $logger
     */
    public function __construct(
        HitRepositoryInterface $hitsRepository,
        HitPageRepositoryInterface $pageRepo,
        HitLinkRepository $linkRepo,
        UserSessionRepository $sessionRepo,
        HitService $service,
        NotificationHelper $notification,
        LoggerInterface $logger
    ) {
        $this->hitsRepository = $hitsRepository;
        $this->pageRepo       = $pageRepo;
        $this->linkRepo       = $linkRepo;
        $this->sessionRepo    = $sessionRepo;
        $this->service        = $service;
        $this->notification   = $notification;
        $this->logger         = $logger;

        parent::__construct();
    }

    public function defineOptions(): array
    {
        // No cli arguments
        return [];
    }

    /**
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function run(): void
    {
        // Run every 5 minutes

        $firstNotProcessed = $this->hitsRepository->getFirstNotProcessed();

        if (!$firstNotProcessed) {
            // Nothing to do
            return;
        }

        $firstHitTimestamp = $firstNotProcessed->getCreatedAt();

        // Process missing targets
        foreach ($this->hitsRepository->getPending(1000) as $hit) {
            try {
                $this->processCounters($hit);
                $this->processMissingTarget($hit, $firstHitTimestamp);
                $this->processNewSource($hit, $firstHitTimestamp);

                $this->processed[] = $hit;
            } catch (\Throwable $e) {
                LoggerHelper::logRawException($this->logger, $e);
            }
        }

//        if (count($this->newMissingTargets) > 0 || count($this->newMissingSources) > 0) {
//            // Notify moderators about new missed URL
//            $this->notification->broadcastMessage(self::MISSING_TARGETS, [
//                'targets' => \array_map(static function (HitPage $page) {
//                    return (string)$page->getFullUrl();
//                }, $this->newMissingTargets),
//
//                'sources' => \array_map(static function (HitPage $page) {
//                    return (string)$page->getFullUrl();
//                }, $this->newMissingSources),
//            ]);
//
//            $this->logger->debug(':count missing targets processed', [
//                ':count' => \count($this->newMissingTargets),
//            ]);
//        }

        // Mark all records as "processed"
        foreach ($this->processed as $hit) {
            if ($hit->isProcessed()) {
                continue;
            }

            // Bind to User if exists
            if ($hit->hasSessionToken()) {
                // Session may be cleaned by GC at this time (server halted, gc issue, etc)
                $session = $this->sessionRepo->findByToken($hit->getSessionToken());

                if ($session && $session->hasUser()) {
                    $hit->setCreatedBy($session->getUser());
                }
            }

            $hit->markAsProcessed();

            $this->hitsRepository->save($hit);
        }


//        if (count($this->newSources) > 0) {
//            // Notify moderators about new referrers
//            $this->notification->broadcastMessage(self::NEW_SOURCES, [
//                'sources' => \array_map(static function (HitPage $page) {
//                    return (string)$page->getFullUrl();
//                }, $this->newSources),
//            ]);
//
//            $this->logger->debug(':count new sources processed', [
//                ':count' => \count($this->newSources),
//            ]);
//        }
    }

    private function processCounters(HitInterface $hit): void
    {
        $moment = $hit->getCreatedAt();
        $target = $hit->getTargetPage();

        // Increment hit counter for target URL
        $target
            ->incrementHits()
            ->setLastSeenAt($moment);

        $this->pageRepo->save($target);

        // Process source page if exists
        if ($hit->hasSourcePage()) {
            $source = $hit->getSourcePage();

            $source->setLastSeenAt($moment);

            // If source page is missing, mark it as existing
            if ($source->isMissing()) {
                $source->markAsOk();
            }

            $this->pageRepo->save($source);

            // Register link
            $this->processLink($source, $target, $moment);
        }
    }

    private function processLink(HitPageInterface $source, HitPageInterface $target, \DateTimeImmutable $moment): void
    {
        $link = $this->service->getLinkBySourceAndTarget($source, $target);

        // Increment link click counter
        $link->incrementClicks();

        if (!$link->getFirstSeenAt()) {
            $link->setFirstSeenAt($moment);
        }

        $link->setLastSeenAt($moment);

        $this->linkRepo->save($link);
    }

    /**
     * @param \BetaKiller\Model\HitInterface $hit
     *
     * @param \DateTimeImmutable             $firstHitTimestamp
     */
    private function processMissingTarget(HitInterface $hit, \DateTimeImmutable $firstHitTimestamp): void
    {
        $target   = $hit->getTargetPage();
        $targetID = $target->getID();

        if (!$target->isMissing() || $target->isIgnored()) {
            return;
        }

        if (!isset($this->newMissingTargets[$targetID]) && $target->getFirstSeenAt() >= $firstHitTimestamp) {
            $this->newMissingTargets[$targetID] = $target;
        }

        if (!$hit->hasSourcePage()) {
            return;
        }

        $source   = $hit->getSourcePage();
        $sourceID = $source->getID();

        if ($source->isIgnored()) {
            return;
        }

        if (!isset($this->newMissingSources[$sourceID]) && $source->getFirstSeenAt() >= $firstHitTimestamp) {
            $this->newMissingSources[$sourceID] = $source;
        }
    }

    private function processNewSource(HitInterface $hit, \DateTimeImmutable $firstHitTimestamp): void
    {
        if (!$hit->hasSourcePage()) {
            return;
        }

        $source   = $hit->getSourcePage();
        $sourceID = $source->getID();

        if ($source->isIgnored()) {
            return;
        }

        if (!isset($this->newSources[$sourceID]) && $source->getFirstSeenAt() >= $firstHitTimestamp) {
            $this->newSources[$sourceID] = $source;
        }
    }
}
