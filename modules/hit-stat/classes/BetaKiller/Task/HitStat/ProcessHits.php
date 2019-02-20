<?php
declare(strict_types=1);

namespace BetaKiller\Task\HitStat;

use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Model\Hit;
use BetaKiller\Model\HitPage;
use BetaKiller\Repository\HitRepository;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

class ProcessHits extends AbstractTask
{
    use LoggerHelperTrait;

    public const MISSING_TARGETS = 'admin/hit-stat/missing-targets';
    public const NEW_SOURCES     = 'admin/hit-stat/new-sources';

    /**
     * @var \BetaKiller\Repository\HitRepository
     */
    private $hitsRepository;

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
     * ProcessHits constructor.
     *
     * @param \BetaKiller\Repository\HitRepository  $hitsRepository
     * @param \BetaKiller\Helper\NotificationHelper $notification
     * @param \Psr\Log\LoggerInterface              $logger
     */
    public function __construct(
        HitRepository $hitsRepository,
        NotificationHelper $notification,
        LoggerInterface $logger
    ) {
        $this->hitsRepository = $hitsRepository;
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

        $firstHitTimestamp = $firstNotProcessed->getTimestamp();

        // Process missing targets
        foreach ($this->hitsRepository->getPending(10000) as $hit) {
            try {
                $this->processMissingTarget($hit, $firstHitTimestamp);
                $this->processNewSource($hit, $firstHitTimestamp);

                $this->processed[] = $hit;
            } catch (\Throwable $e) {
                $this->logException($this->logger, $e);
            }
        }

        if ($this->newMissingTargets) {
            // Notify moderators about new missed URL
            $this->notification->groupMessage(self::MISSING_TARGETS, [
                'targets' => \array_map(function (HitPage $page) {
                    return $page->getFullUrl();
                }, $this->newMissingTargets),

                'sources' => \array_map(function (HitPage $page) {
                    return $page->getFullUrl();
                }, $this->newMissingSources),
            ]);

            $this->logger->debug(':count missing targets processed', [
                ':count' => \count($this->newMissingTargets),
            ]);
        }

        if ($this->newSources) {
            // Notify moderators about new referrers
            $this->notification->groupMessage(self::NEW_SOURCES, [
                'sources' => \array_map(function (HitPage $page) {
                    return $page->getFullUrl();
                }, $this->newSources),
            ]);

            $this->logger->debug(':count new sources processed', [
                ':count' => \count($this->newSources),
            ]);
        }

        // Mark all records as "processed"
        foreach ($this->processed as $hit) {
            if ($hit->isProcessed()) {
                continue;
            }

            $hit->markAsProcessed();

            $this->hitsRepository->save($hit);
        }
    }

    /**
     * @param \BetaKiller\Model\Hit $hit
     *
     * @param \DateTimeImmutable    $firstHitTimestamp
     */
    private function processMissingTarget(Hit $hit, \DateTimeImmutable $firstHitTimestamp): void
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

    private function processNewSource(Hit $hit, \DateTimeImmutable $firstHitTimestamp): void
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
