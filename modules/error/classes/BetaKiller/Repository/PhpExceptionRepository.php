<?php

namespace BetaKiller\Repository;

use BetaKiller\Model\PhpException;
use BetaKiller\Model\PhpExceptionModelInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use Database;
use DB;

/**
 * Class PhpExceptionRepository
 *
 * @package BetaKiller\Error
 * @method PhpExceptionModelInterface getById(int $id)
 */
class PhpExceptionRepository extends AbstractOrmBasedDispatchableRepository implements PhpExceptionRepositoryInterface
{
    use SqliteOrmRepositoryTrait;

    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return 'hash';
    }

    protected function getDatabaseGroup(): string
    {
        return PhpException::DB_GROUP;
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

    protected function createTableIfNotExists(): void
    {
        $this->createErrorsTableIfNotExists();
        $this->createErrorHistoryTableIfNotExists();
    }

    protected function createErrorsTableIfNotExists()
    {
        DB::query(
            Database::SELECT,
            'CREATE TABLE IF NOT EXISTS errors (
          id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
          hash VARCHAR(64) NOT NULL,
          urls TEXT NULL,
          paths TEXT NULL,
          modules TEXT NULL,
          created_at DATETIME NOT NULL,
          last_seen_at DATETIME NOT NULL,
          last_notified_at DATETIME NULL,
          resolved_by INTEGER UNSIGNED NULL,
          status VARCHAR(16) NOT NULL,
          message TEXT NOT NULL,
          trace BLOB NULL,
          total INTEGER UNSIGNED NOT NULL DEFAULT 0,
          notification_required UNSIGNED INTEGER(1) NOT NULL DEFAULT 0
        )'
        )->execute(PhpException::DB_GROUP);
    }

    protected function createErrorHistoryTableIfNotExists()
    {
        DB::query(
            Database::SELECT,
            'CREATE TABLE IF NOT EXISTS error_history (
          id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
          error_id INTEGER NOT NULL,
          user INTEGER NULL,
          ts DATETIME NOT NULL,
          status VARCHAR(16) NOT NULL,
          FOREIGN KEY(error_id) REFERENCES errors(id)
        )'
        )->execute(PhpException::DB_GROUP);
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
