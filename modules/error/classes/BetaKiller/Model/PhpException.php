<?php
namespace BetaKiller\Model;

use Database;
use DateTime;
use DateTimeImmutable;
use DB;
use ORM;

class PhpException extends \ORM implements PhpExceptionModelInterface
{
    public const COLUMN_HASH         = 'hash';
    public const COLUMN_STATUS       = 'status';
    public const COLUMN_CREATED_AT   = 'created_at';
    public const COLUMN_LAST_SEEN_AT = 'last_seen_at';
    public const COLUMN_MESSAGE      = 'message';

    private static $tablesChecked = false;

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function configure(): void
    {
        $this->_db_group   = 'errors';
        $this->_table_name = 'errors';

        /**
         * Auto-serialize and unserialize columns on get/set
         *
         * @var array
         */
        $this->_serialize_columns = [
            'urls',
            'paths',
            'modules',
        ];

        $this->has_many([
            'history' => [
                'model'       => 'PhpExceptionHistory',
                'foreign_key' => 'error_id',
            ],
        ]);

        $this->createTablesIfNotExists();
    }

    protected function createTablesIfNotExists()
    {
        if (!static::$tablesChecked) {
            $this->createErrorsTableIfNotExists();
            $this->createErrorHistoryTableIfNotExists();
            static::$tablesChecked = true;
        }
    }

    protected function createErrorsTableIfNotExists()
    {
        DB::query(Database::SELECT, 'CREATE TABLE IF NOT EXISTS errors (
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
          trace BLOB NOT NULL,
          total INTEGER UNSIGNED NOT NULL DEFAULT 0,
          notification_required UNSIGNED INTEGER(1) NOT NULL DEFAULT 0
        )')->execute($this->_db_group);
    }

    protected function createErrorHistoryTableIfNotExists()
    {
        DB::query(Database::SELECT, 'CREATE TABLE IF NOT EXISTS error_history (
          id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
          error_id INTEGER NOT NULL,
          user INTEGER NULL,
          ts DATETIME NOT NULL,
          status VARCHAR(16) NOT NULL,
          FOREIGN KEY(error_id) REFERENCES errors(id)
        )')->execute($this->_db_group);
    }

    /**
     * Rule definitions for validation
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            self::COLUMN_HASH => [
                ['not_empty'],
                ['max_length', [':value', 64]],
            ],

            self::COLUMN_STATUS => [
                ['not_empty'],
                ['max_length', [':value', 16]],
            ],

            self::COLUMN_CREATED_AT => [
                ['not_empty'],
            ],

            self::COLUMN_LAST_SEEN_AT => [
                ['not_empty'],
            ],

            self::COLUMN_MESSAGE => [
                ['not_empty'],
            ],
        ];
    }

    /**
     * @param string $module
     *
     * @return $this
     */
    public function addModule(string $module): PhpExceptionModelInterface
    {
        $modules = $this->getModules();

        // Skip adding if provided path was added already
        if (!$module || \in_array($module, $modules, true)) {
            return $this;
        }

        $modules[] = $module;

        $this->setModules($modules);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getModules(): array
    {
        return (array)$this->get('modules');
    }

    private function setModules(array $modules)
    {
        $this->set('modules', $modules);

        return $this;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->get(self::COLUMN_HASH);
    }

    public function setHash(string $value): PhpExceptionModelInterface
    {
        $this->set(self::COLUMN_HASH, $value);

        return $this;
    }

    /**
     * @return int
     */
    public function getCounter(): int
    {
        return $this->loaded() ? $this->getTotal() : 0;
    }

    /**
     * @return int
     */
    private function getTotal(): int
    {
        return (int)$this->get('total');
    }

    private function setTotal(int $value): PhpExceptionModelInterface
    {
        $this->set('total', $value);

        return $this;
    }

    /**
     * @return $this
     */
    public function incrementCounter(): PhpExceptionModelInterface
    {
        return $this->setTotal($this->getTotal() + 1);
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->get(self::COLUMN_MESSAGE);
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMessage(string $value): PhpExceptionModelInterface
    {
        $this->set(self::COLUMN_MESSAGE, $value);

        return $this;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function addPath(string $path): PhpExceptionModelInterface
    {
        $paths = $this->getPaths();

        // Skip adding if provided path was added already
        if (!$path || \in_array($path, $paths, true)) {
            return $this;
        }

        $paths[] = $path;

        $this->setPaths($paths);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getPaths(): array
    {
        return (array)$this->get('paths');
    }

    private function setPaths(array $paths): PhpExceptionModelInterface
    {
        $this->set('paths', $paths);

        return $this;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function addUrl(string $url): PhpExceptionModelInterface
    {
        $urls = $this->getUrls();

        // Skip adding if provided path was added already
        if (!$url || \in_array($url, $urls, true)) {
            return $this;
        }

        $urls[] = $url;

        $this->setUrls($urls);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getUrls(): array
    {
        return (array)$this->get('urls');
    }

    private function setUrls(array $urls): PhpExceptionModelInterface
    {
        $this->set('urls', $urls);

        return $this;
    }

    /**
     * @return string
     */
    public function getTrace(): string
    {
        return $this->get('trace');
    }

    /**
     * @param string $formattedTrace
     *
     * @return \BetaKiller\Model\PhpExceptionModelInterface
     */
    public function setTrace(string $formattedTrace): PhpExceptionModelInterface
    {
        $this->set('trace', $formattedTrace);

        return $this;
    }

    /**
     * @param \DateTimeInterface|NULL $time
     *
     * @return $this
     */
    public function setCreatedAt(\DateTimeInterface $time): PhpExceptionModelInterface
    {
        $this->set_datetime_column_value(self::COLUMN_CREATED_AT, $time);

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->get_datetime_column_value(self::COLUMN_CREATED_AT);
    }

    /**
     * Unix timestamp of last notification time
     *
     * @param \DateTimeInterface|NULL $time
     *
     * @return $this
     */
    public function setLastSeenAt(\DateTimeInterface $time): PhpExceptionModelInterface
    {
        $this->set_datetime_column_value(self::COLUMN_LAST_SEEN_AT, $time);

        return $this;
    }

    /**
     * Unix timestamp of last notification time
     *
     * @return \DateTimeImmutable
     */
    public function getLastSeenAt(): DateTimeImmutable
    {
        return $this->get_datetime_column_value(self::COLUMN_LAST_SEEN_AT);
    }


    /**
     * Unix timestamp of last notification time
     *
     * @param \DateTimeInterface $time
     *
     * @return $this
     */
    public function setLastNotifiedAt(\DateTimeInterface $time): PhpExceptionModelInterface
    {
        $this->set_datetime_column_value('last_notified_at', $time);

        return $this;
    }

    /**
     * Unix timestamp of last notification time
     *
     * @return \DateTimeImmutable|NULL
     */
    public function getLastNotifiedAt(): ?DateTimeImmutable
    {
        return $this->get_datetime_column_value('last_notified_at');
    }

    private function setResolvedBy(?UserInterface $user): PhpExceptionModelInterface
    {
        $this->set('resolved_by', $user ? $user->getID() : null);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getResolvedByUserID(): ?string
    {
        $id = $this->get('resolved_by');

        return $id ? (string)$id : null;
    }

    /**
     * Mark exception as new (these exceptions require developer attention)
     *
     * @param UserInterface|null $user
     *
     * @return $this
     */
    public function markAsNew(?UserInterface $user): PhpExceptionModelInterface
    {
        $this->setStatus(self::STATE_NEW);
        $this->addHistoryRecord($user);

        return $this;
    }

    /**
     * Mark exception as repeated (it was resolved earlier but repeated now)
     *
     * @param UserInterface|null $user
     *
     * @return $this
     */
    public function markAsRepeated(?UserInterface $user): PhpExceptionModelInterface
    {
        // Skip if exception was not resolved yet
        if (!$this->isResolved()) {
            return $this;
        }

        // Reset resolved_by
        $this->setResolvedBy(null);
        $this->setStatus(self::STATE_REPEATED);
        $this->addHistoryRecord($user);

        return $this;
    }

    /**
     * Mark exception as resolved
     *
     * @param UserInterface $user
     *
     * @return $this
     */
    public function markAsResolvedBy(UserInterface $user): PhpExceptionModelInterface
    {
        // Reset resolved_by
        $this->setResolvedBy($user);
        $this->setStatus(self::STATE_RESOLVED);
        $this->addHistoryRecord($user);

        return $this;
    }

    /**
     * Mark exception as ignored
     *
     * @param UserInterface $user
     *
     * @return $this
     */
    public function markAsIgnoredBy(UserInterface $user): PhpExceptionModelInterface
    {
        // Reset resolved_by
        $this->setResolvedBy(null);
        $this->setStatus(self::STATE_IGNORED);
        $this->addHistoryRecord($user);

        return $this;
    }

    /**
     * Returns TRUE if exception was resolved
     *
     * @return bool
     */
    public function isResolved(): bool
    {
        return $this->getStatus() === self::STATE_RESOLVED;
    }

    /**
     * Returns TRUE if current exception is in 'repeat' state
     *
     * @return bool
     */
    public function isRepeated(): bool
    {
        return $this->getStatus() === self::STATE_REPEATED;
    }

    /**
     * Returns TRUE if current exception is in 'new' state
     *
     * @return bool
     */
    public function isNew(): bool
    {
        return !$this->getID() || $this->getStatus() === self::STATE_NEW;
    }

    /**
     * Returns TRUE if current exception is in 'ignored' state
     *
     * @return bool
     */
    public function isIgnored(): bool
    {
        return $this->getStatus() === self::STATE_IGNORED;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->get(self::COLUMN_STATUS);
    }

    /**
     * @param string $status
     *
     * @return PhpExceptionModelInterface
     */
    private function setStatus(string $status): PhpExceptionModelInterface
    {
        $this->set(self::COLUMN_STATUS, $status);

        return $this;
    }

    /**
     * @return PhpExceptionHistory
     */
    private function getHistoryRelation(): PhpExceptionHistory
    {
        return $this->get('history');
    }

    /**
     * @return PhpExceptionHistoryModelInterface[]
     */
    public function getHistoricalRecords(): array
    {
        return $this->getHistoryRelation()->get_all();
    }

    /**
     * Marks current exception instance as "notification required"
     */
    public function notificationRequired(): void
    {
        $this->set('notification_required', true);
    }

    /**
     * Marks current exception instance as "notification required" = 0
     */
    public function wasNotified(): void
    {
        $this->set('notification_required', false);
    }

    /**
     * Returns true if someone needs to be notified about current exception instance
     *
     * @return bool
     */
    public function isNotificationRequired(): bool
    {
        return (bool)$this->get('notification_required');
    }

    /**
     * Adds record to history
     *
     * @param UserInterface|null $user
     *
     * @return \BetaKiller\Model\PhpExceptionHistoryModelInterface
     * @internal
     */
    private function addHistoryRecord(UserInterface $user = null): PhpExceptionHistoryModelInterface
    {
        // Get error ID for new records
        $this->save();

        $historyModel = new PhpExceptionHistory;

        return $historyModel
            ->setPhpException($this)
            ->setStatus($this->getStatus())
            ->setTimestamp(new DateTime)
            ->setUser($user)
            ->save();
    }

    /**
     * Deletes a single record while ignoring relationships.
     *
     * @chainable
     * @return ORM
     */
    public function delete(): ORM
    {
        // Delete historical records coz SQLite can not do it automatically
        foreach ($this->getHistoricalRecords() as $history) {
            $history->delete();
        }

        return parent::delete();
    }
}
