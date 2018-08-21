<?php
declare(strict_types=1);

namespace BetaKiller\Task\Ref;

use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Model\RefHit;
use BetaKiller\Repository\RefHitRepository;
use BetaKiller\Repository\RefLinkRepository;
use BetaKiller\Repository\RefPageRepository;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

class ProcessHits extends AbstractTask
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Repository\RefHitRepository
     */
    private $hitsRepository;

    /**
     * @var \BetaKiller\Repository\RefPageRepository
     */
    private $pageRepository;

    /**
     * @var \BetaKiller\Repository\RefLinkRepository
     */
    private $linkRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * ProcessHits constructor.
     *
     * @param \BetaKiller\Repository\RefHitRepository  $hitsRepository
     * @param \BetaKiller\Repository\RefPageRepository $pageRepository
     * @param \BetaKiller\Repository\RefLinkRepository $linkRepository
     * @param \Psr\Log\LoggerInterface                 $logger
     */
    public function __construct(
        RefHitRepository $hitsRepository,
        RefPageRepository $pageRepository,
        RefLinkRepository $linkRepository,
        LoggerInterface $logger
    ) {
        $this->hitsRepository = $hitsRepository;
        $this->pageRepository = $pageRepository;
        $this->linkRepository = $linkRepository;
        $this->logger         = $logger;

        parent::__construct();
    }

    /**
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function run(): void
    {
        foreach ($this->hitsRepository->getPending() as $record) {
            try {
                $this->processRefLogRecord($record);

                $record->markAsProcessed();

                $this->hitsRepository->save($record);
            } catch (\Throwable $e) {
                $this->logException($this->logger, $e);
            }
        }

//         Temporarily disable cleanup to keep data for ongoing features
//        $this->hitsRepository->deleteProcessed();
    }

    /**
     * @param \BetaKiller\Model\RefHit $record
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function processRefLogRecord(RefHit $record): void
    {
        $sourceUrl = $record->getSourceUrl();
        $targetUrl = $record->getTargetUrl();

        // Find source page
        $sourcePage = $sourceUrl ? $this->pageRepository->findByFullUrl($sourceUrl) : null;

        // Skip ignored pages and domains
        if ($sourcePage && $sourcePage->isIgnored()) {
            return;
        }

        // Search for target URL and create if not exists
        $targetPage = $this->pageRepository->findByFullUrl($targetUrl);

        // Increment hit counter for target URL
        $targetPage->incrementHits();
        $this->pageRepository->save($targetPage);

        // Register link
        $link = $this->linkRepository->findBySourceAndTarget($sourcePage, $targetPage);

        // Increment link click counter
        $link->incrementClicks();

        if (!$link->getFirstSeenAt()) {
            $link->setFirstSeenAt($record->getTimestamp());
        }

        $link->setLastSeenAt($record->getTimestamp());

        $this->linkRepository->save($link);
        // TODO Deal with redirects from http to https (this would double counters)
    }
}
