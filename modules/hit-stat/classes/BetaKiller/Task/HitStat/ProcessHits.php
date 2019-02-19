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

    public const NOTIFICATION = 'admin/hit-stat/missing';

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
    private $missingTargets = [];

    /**
     * @var \BetaKiller\Model\HitPage[]
     */
    private $missingSources = [];

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
        foreach ($this->hitsRepository->getPending(1000) as $hit) {
            try {
                $this->processMissingHit($hit, $firstHitTimestamp);

                $this->processed[] = $hit;
            } catch (\Throwable $e) {
                $this->logException($this->logger, $e);
            }
        }

        if ($this->missingTargets) {
            // Notify moderators about new missed URL and referrers
            // Notify moderators about new referrers in missing URL
            $this->notification->groupMessage(self::NOTIFICATION, [
                'sources' => \array_map(function (HitPage $page) {
                    return $page->getFullUrl();
                }, $this->missingSources),

                'targets' => \array_map(function (HitPage $page) {
                    return $page->getFullUrl();
                }, $this->missingTargets),
            ]);

            $this->logger->debug(':count missing targets processed', [
                ':count' => \count($this->missingTargets),
            ]);

            if ($this->missingSources) {
                $this->logger->debug(':count new missing sources processed', [
                    ':count' => \count($this->missingSources),
                ]);
            }
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
    private function processMissingHit(Hit $hit, \DateTimeImmutable $firstHitTimestamp): void
    {
        $target   = $hit->getTargetPage();
        $targetID = $target->getID();

        if (!$target->isMissing()) {
            return;
        }

        if (!isset($this->missingTargets[$targetID])) {
            $this->missingTargets[$targetID] = $target;
        }

        if ($hit->hasSourcePage()) {
            $source   = $hit->getSourcePage();
            $sourceID = $source->getID();

            if (!isset($this->missingSources[$sourceID]) && $source->getFirstSeenAt() >= $firstHitTimestamp) {
                $this->missingSources[$sourceID] = $source;
            }
        }
    }
}
