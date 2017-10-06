<?php
namespace BetaKiller\Task\Ref;

use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Model\RefHit;
use BetaKiller\Task\AbstractTask;

class ProcessHits extends AbstractTask
{
    use LoggerHelperTrait;

    /**
     * @Inject
     * @var \BetaKiller\Repository\RefHitsRepository
     */
    private $hitsRepository;

    /**
     * @Inject
     * @var \BetaKiller\Repository\RefInternalPageRepository
     */
    private $internalPageRepository;

    /**
     * @Inject
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

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

        $this->hitsRepository->deleteProcessed();
    }

    private function processRefLogRecord(RefHit $record): void
    {
        $sourceUrl = $record->getSourceUrl();
        $targetUrl = $record->getTargetUrl();

        // Search for target URL and create if not exists
        $targetPage = $this->internalPageRepository->getByUrl($targetUrl);

        if (!$targetPage) {
            $targetPage = $this->internalPageRepository->create()
                ->setUri($targetUrl);
        }

        // Increment hit counter for target URL
        $targetPage->incrementHits();
        $this->internalPageRepository->save($targetPage);

        // Get current domain
        $siteUrl = $this->appConfig->getBaseUrl();
        $siteDomain = parse_url($siteUrl, PHP_URL_HOST);

        // TODO Deal with redirects from http to https (this would double counters)

        // Internal hit? Increment counter and create record if not exists

        // External hit? Increment counter and create record if not exists
    }
}
