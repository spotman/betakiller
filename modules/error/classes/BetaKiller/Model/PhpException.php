<?php
namespace BetaKiller\Model;

use BetaKiller\Exception;
use BetaKiller\Helper\UserModelFactoryTrait;
use Database;
use DateTime;
use DateTimeImmutable;
use DB;
use Kohana_Exception;
use ORM;

class PhpException extends \ORM implements PhpExceptionModelInterface
{
    use UserModelFactoryTrait;

    private static $_tablesChecked = false;

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function _initialize(): void
    {
        $this->_db_group   = 'filesystem';
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

        parent::_initialize();
    }

    protected function createTablesIfNotExists()
    {
        if (!static::$_tablesChecked) {
            $this->createErrorsTableIfNotExists();
            $this->createErrorHistoryTableIfNotExists();
            static::$_tablesChecked = true;
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
     * @param string $module
     *
     * @return $this
     */
    public function addModule(string $module)
    {
        $modules = $this->getModules();

        // Skip adding if provided path was added already
        if (!$module || in_array($module, $modules, true)) {
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

    protected function setModules(array $modules)
    {
        $this->set('modules', $modules);

        return $this;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->get('hash');
    }

    public function setHash(string $value)
    {
        $this->set('hash', $value);

        return $this;
    }

    /**
     * @return int
     */
    public function getCounter(): int
    {
        return $this->loaded() ? (int)$this->getTotal() : 0;
    }

    /**
     * @return int
     */
    protected function getTotal(): int
    {
        return (int)$this->get('total');
    }

    protected function setTotal(int $value)
    {
        $this->set('total', $value);

        return $this;
    }

    /**
     * @return $this
     */
    public function incrementCounter()
    {
        return $this->setTotal($this->getTotal() + 1);
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->getMessage();
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->get('message');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMessage(string $value)
    {
        $this->set('message', $value);

        return $this;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function addPath(string $path)
    {
        $paths = $this->getPaths();

        // Skip adding if provided path was added already
        if (!$path || in_array($path, $paths, true)) {
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

    protected function setPaths(array $paths)
    {
        $this->set('paths', $paths);

        return $this;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function addUrl(string $url)
    {
        $urls = $this->getUrls();

        // Skip adding if provided path was added already
        if (!$url || in_array($url, $urls, true)) {
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

    protected function setUrls(array $urls)
    {
        $this->set('urls', $urls);

        return $this;
    }

    public function hasTrace(): bool
    {
        $path = $this->getTraceFullPath();

        return file_exists($path);
    }

    /**
     * @return string
     */
    public function getTrace(): string
    {
        $path = $this->getTraceFullPath();

        return file_get_contents($path);
    }

    private function getTraceFullPath(): string
    {
        $dir = MODPATH.'error/media/php_traces';

        if (!file_exists($dir) && !@mkdir($dir, 0664, true) && !is_dir($dir)) {
            throw new Exception('Can not create directory [:dir] for php stacktrace files', [':dir' => $dir]);
        }

        return $dir.DIRECTORY_SEPARATOR.$this->getHash();
    }


    /**
     * @param string $formattedTrace
     *
     * @return \BetaKiller\Model\PhpExceptionModelInterface
     */
    public function setTrace(string $formattedTrace): PhpExceptionModelInterface
    {
        $path = $this->getTraceFullPath();
        file_put_contents($path, $formattedTrace);

        return $this;
    }

    public function deleteTrace(): void
    {
        unlink($this->getTraceFullPath());
    }

    /**
     * @param \DateTimeInterface|NULL $time
     *
     * @return $this
     */
    public function setCreatedAt(\DateTimeInterface $time)
    {
        $this->set_datetime_column_value('created_at', $time);

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->get_datetime_column_value('created_at');
    }

    /**
     * Unix timestamp of last notification time
     *
     * @param \DateTimeInterface|NULL $time
     *
     * @return $this
     */
    public function setLastSeenAt(\DateTimeInterface $time)
    {
        $this->set_datetime_column_value('last_seen_at', $time);

        return $this;
    }

    /**
     * Unix timestamp of last notification time
     *
     * @return \DateTimeImmutable
     */
    public function getLastSeenAt(): DateTimeImmutable
    {
        return $this->get_datetime_column_value('last_seen_at');
    }


    /**
     * Unix timestamp of last notification time
     *
     * @param \DateTimeInterface $time
     *
     * @return $this
     */
    public function setLastNotifiedAt(\DateTimeInterface $time)
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

    protected function setResolvedBy(?UserInterface $user)
    {
        $this->set('resolved_by', $user ? $user->getID() : null);

        return $this;
    }

    /**
     * @return UserInterface|null
     */
    public function getResolvedBy(): ?UserInterface
    {
        $id = $this->get('resolved_by');

        return $id ? $this->model_factory_user($id) : null;
    }

    /**
     * Mark exception as new (these exceptions require developer attention)
     *
     * @param UserInterface|null $user
     *
     * @return $this
     */
    public function markAsNew(UserInterface $user)
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
    public function markAsRepeated(UserInterface $user)
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
    public function markAsResolvedBy(UserInterface $user)
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
    public function markAsIgnoredBy(UserInterface $user)
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
        return $this->get('status');
    }

    /**
     * @param string $status
     *
     * @return PhpExceptionModelInterface
     */
    protected function setStatus($status): PhpExceptionModelInterface
    {
        $this->set('status', (string)$status);

        return $this;
    }

    /**
     * @return PhpExceptionHistory
     */
    protected function getHistoryRelation(): PhpExceptionHistory
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
     * @internal param string $type What have been done
     */
    protected function addHistoryRecord(UserInterface $user = null): PhpExceptionHistoryModelInterface
    {
        // Get error ID for new records
        $this->save();

        /** @var \BetaKiller\Model\PhpExceptionHistory $historyModel */
        $historyModel = $this->getHistoryRelation()->model_factory();

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
     * @throws Kohana_Exception
     * @return ORM
     */
    public function delete(): ORM
    {
        $this->deleteTrace();

        // Delete historical records coz SQLite can not do it automatically
        foreach ($this->getHistoricalRecords() as $history) {
            $history->delete();
        }

        return parent::delete();
    }
}
