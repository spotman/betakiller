<?php
declare(strict_types=1);

namespace BetaKiller\CommandHandler;

use BetaKiller\Command\HitStatStoreCommand;
use BetaKiller\Model\Hit;
use BetaKiller\Model\HitPageInterface;
use BetaKiller\Repository\HitLinkRepository;
use BetaKiller\Repository\HitPageRepository;
use BetaKiller\Repository\HitRepository;
use BetaKiller\Service\HitService;

final class HitStatStoreCommandHandler
{
    /**
     * @var \BetaKiller\Repository\HitPageRepository
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
     * @var \BetaKiller\Repository\HitRepository
     */
    private $hitRepo;

    /**
     * HitStatStoreCommandHandler constructor.
     *
     * @param \BetaKiller\Repository\HitPageRepository $pageRepo
     * @param \BetaKiller\Repository\HitLinkRepository $linkRepo
     * @param \BetaKiller\Repository\HitRepository     $hitRepo
     * @param \BetaKiller\Service\HitService           $service
     */
    public function __construct(
        HitPageRepository $pageRepo,
        HitLinkRepository $linkRepo,
        HitRepository $hitRepo,
        HitService $service
    ) {
        $this->pageRepo = $pageRepo;
        $this->linkRepo = $linkRepo;
        $this->service  = $service;
        $this->hitRepo  = $hitRepo;
    }

    public function __invoke(HitStatStoreCommand $command): void
    {
        $source = $command->getSource();
        $target = $command->getTarget();
        $marker = $command->getMarker();
        $moment = $command->getMoment();
        $ip     = $command->getIp();
        $uuid   = $command->getUuid();
        $user   = $command->getUser();

        // Ignore hits of admin users
        if ($user->hasAdminRole()) {
            return;
        }

        // Skip ignored pages and domains
        if ($source && $source->isIgnored()) {
            return;
        }

        // Skip ignored pages and domains
        if ($target->isIgnored()) {
            return;
        }

        // Increment hit counter for target URL
        $target
            ->incrementHits()
            ->setLastSeenAt($moment);

        $this->pageRepo->save($target);

        // Process source page if exists
        if ($source) {
            $source->setLastSeenAt($moment);

            // If source page is missing, mark it as existing
            if ($source->isMissing()) {
                $source->markAsOk();
            }

            $this->pageRepo->save($source);

            // Register link
            $this->processLink($source, $target, $moment);
        }

        // Create new Hit object with source/target pages, marker, ip and other info
        $hit = new Hit;

        // Store User and hit UUID
        $hit->setUuid($uuid);
        $hit->bindToUser($user);

        $hit
            ->setTargetPage($target)
            ->setIP($ip)
            ->setTimestamp($moment);

        if ($source) {
            $hit->setSourcePage($source);
        }

        if ($marker) {
            $hit->setTargetMarker($marker);
        }

        $this->hitRepo->save($hit);
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
}