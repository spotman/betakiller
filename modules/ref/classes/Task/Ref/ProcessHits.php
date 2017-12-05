<?php

use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Model\RefHit;
use BetaKiller\Task\AbstractTask;

class Task_Ref_ProcessHits extends AbstractTask
{
    use LoggerHelperTrait;

    /**
     * @Inject
     * @var \BetaKiller\Repository\RefHitRepository
     */
    private $hitsRepository;

    /**
     * @Inject
     * @var \BetaKiller\Repository\RefPageRepository
     */
    private $pageRepository;

    /**
     * @Inject
     * @var \BetaKiller\Repository\RefLinkRepository
     */
    private $linkRepository;

    /**
     * @param array $params
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function _execute(array $params): void
    {
        $records = $this->hitsRepository->getPending();

        foreach ($records as $record) {
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
