<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\PhpExceptionModelInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

/**
 * Class PhpExceptionRepository
 *
 * @package BetaKiller\Error
 * @method PhpExceptionModelInterface getById(int $id)
 */
class PhpExceptionRepository extends AbstractOrmBasedDispatchableRepository implements PhpExceptionRepositoryInterface
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return 'hash';
    }

    /**
     * @return PhpExceptionModelInterface[]
     */
    public function getUnresolvedPhpExceptions(): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterUnresolved($orm)
            ->orderByLastSeenAt($orm)
            ->findAll($orm);
    }

    /**
     * @return PhpExceptionModelInterface[]
     */
    public function getResolvedPhpExceptions(): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterResolved($orm)
            ->orderByLastSeenAt($orm)
            ->findAll($orm);
    }

    /**
     * @return PhpExceptionModelInterface[]
     */
    public function getIgnoredPhpExceptions(): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterIgnored($orm)
            ->orderByLastSeenAt($orm)
            ->findAll($orm);
    }

    /**
     * @return PhpExceptionModelInterface[]
     */
    public function getRequiredNotification(): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterNotificationRequired($orm)
            ->filterUnresolved($orm)
            ->orderByLastSeenAt($orm)
            ->findAll($orm);
    }

    /**
     * @inheritDoc
     */
    public function getReadyForCleanup(\DateTimeImmutable $before): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterLastSeenBefore($orm, $before)
            ->filterUnresolved($orm)
            ->findAll($orm);
    }

    /**
     * @param string $hash
     *
     * @return PhpExceptionModelInterface|null
     */
    public function findByHash(string $hash): ?PhpExceptionModelInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterHash($orm, $hash)
            ->findOne($orm);
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param string                                    $hash
     *
     * @return \BetaKiller\Repository\PhpExceptionRepository
     */
    private function filterHash(OrmInterface $orm, string $hash): PhpExceptionRepository
    {
        $orm->where('hash', '=', $hash);

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     *
     * @return \BetaKiller\Repository\PhpExceptionRepository
     */
    private function filterUnresolved(OrmInterface $orm): PhpExceptionRepository
    {
        return $this->filterStatuses($orm, [
            PhpExceptionModelInterface::STATE_NEW,
            PhpExceptionModelInterface::STATE_REPEATED,
        ]);
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     *
     * @return \BetaKiller\Repository\PhpExceptionRepository
     */
    private function filterResolved(OrmInterface $orm): PhpExceptionRepository
    {
        return $this->filterStatuses($orm, [
            PhpExceptionModelInterface::STATE_RESOLVED,
        ]);
    }

    private function filterIgnored(OrmInterface $orm): PhpExceptionRepository
    {
        return $this->filterStatuses($orm, [
            PhpExceptionModelInterface::STATE_IGNORED,
        ]);
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     *
     * @return \BetaKiller\Repository\PhpExceptionRepository
     */
    private function filterNotificationRequired(OrmInterface $orm): PhpExceptionRepository
    {
        $orm->where('notification_required', '=', true);

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param string[]                                  $statuses
     *
     * @return \BetaKiller\Repository\PhpExceptionRepository
     */
    private function filterStatuses(OrmInterface $orm, array $statuses): PhpExceptionRepository
    {
        $orm->where('status', 'IN', $statuses);

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param bool|null                                 $asc
     *
     * @return \BetaKiller\Repository\PhpExceptionRepository
     */
    private function orderByLastSeenAt(OrmInterface $orm, ?bool $asc = null): PhpExceptionRepository
    {
        $orm->order_by('last_seen_at', $asc ? 'asc' : 'desc');

        return $this;
    }

    private function filterLastSeenBefore(OrmInterface $orm, \DateTimeImmutable $before): PhpExceptionRepository
    {
        $orm->filter_datetime_column_value('last_seen_at', $before, '<');

        return $this;
    }
}
