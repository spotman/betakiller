<?php
declare(strict_types=1);

namespace BetaKiller\Task\Ref;

use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Model\Hit;
use BetaKiller\Repository\HitRepository;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

class ProcessHits extends AbstractTask
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Repository\HitRepository
     */
    private $hitsRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * ProcessHits constructor.
     *
     * @param \BetaKiller\Repository\HitRepository $hitsRepository
     * @param \Psr\Log\LoggerInterface             $logger
     */
    public function __construct(
        HitRepository $hitsRepository,
        LoggerInterface $logger
    ) {
        $this->hitsRepository = $hitsRepository;
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
        // TODO Run every 5 minutes

        foreach ($this->hitsRepository->getPending() as $record) {
            try {
                $this->processRefLogRecord($record);

                $record->markAsProcessed();

                $this->hitsRepository->save($record);
            } catch (\Throwable $e) {
                $this->logException($this->logger, $e);
            }
        }
    }

    /**
     * @param \BetaKiller\Model\Hit $record
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function processRefLogRecord(Hit $record): void
    {
        // TODO Notify moderators about new missed URL

        // TODO Notify moderators about new referrer in missing URL
    }
}
