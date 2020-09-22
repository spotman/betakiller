<?php
declare(strict_types=1);

namespace BetaKiller\Task\Error;

use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Model\PhpExceptionModelInterface;
use BetaKiller\Repository\PhpExceptionRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Url\ZoneInterface;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

final class Cleanup extends AbstractTask
{

    /**
     * @var \BetaKiller\Repository\PhpExceptionRepositoryInterface
     */
    private PhpExceptionRepositoryInterface $repo;

    /**
     * Cleanup constructor.
     *
     * @param \BetaKiller\Repository\PhpExceptionRepositoryInterface $repo
     */
    public function __construct(PhpExceptionRepositoryInterface $repo)
    {
        $this->repo   = $repo;

        parent::__construct();
    }

    public function defineOptions(): array
    {
        // No cli arguments
        return [];
    }

    public function run(): void
    {
        $before = new DateTimeImmutable('- 2 weeks');

        foreach ($this->repo->getLastSeenBefore($before) as $item) {
            $this->repo->delete($item);
        }
    }
}
