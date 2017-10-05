<?php
namespace BetaKiller\Task\Ref;

use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Model\RefLog;
use BetaKiller\Task\AbstractTask;

class ProcessLog extends AbstractTask
{
    use LoggerHelperTrait;

    /**
     * @Inject
     * @var \BetaKiller\Repository\RefLogRepository
     */
    private $refLogRepository;

    protected function _execute(array $params): void
    {
        $records = $this->refLogRepository->getPending();

        foreach ($records as $record) {
            try {
                $this->processRefLogRecord($record);

                $record->markAsProcessed();

                $this->refLogRepository->save($record);
            } catch (\Throwable $e) {
                $this->logException($this->logger, $e);
            }
        }

        $this->refLogRepository->deleteProcessed();
    }

    private function processRefLogRecord(RefLog $record): void
    {
        // TODO: Implement method.
    }
}
