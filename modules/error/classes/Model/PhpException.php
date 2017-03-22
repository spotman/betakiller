<?php

use BetaKiller\Error\PhpExceptionModelInterface;
use BetaKiller\Error\PhpExceptionHistoryModelInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Helper\UserModelFactoryTrait;
use BetaKiller\Helper\ErrorHelperTrait;

class Model_PhpException extends \ORM implements PhpExceptionModelInterface
{
    use UserModelFactoryTrait;
    use ErrorHelperTrait;

    const URL_PARAM = 'PhpException';

    private static $_tablesChecked = false;

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function _initialize()
    {
        $this->_db_group = 'filesystem';
        $this->_table_name = 'errors';

        /**
         * Auto-serialize and unserialize columns on get/set
         * @var array
         */
        $this->_serialize_columns = [
            'urls',
            'paths',
            'modules',
        ];

        $this->has_many([
            'history'   =>  [
                'model'         =>  'PhpExceptionHistory',
                'foreign_key'   =>  'error_id',
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
          total INTEGER UNSIGNED NOT NULL DEFAULT 0
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
     * @return string
     */
    public function getID()
    {
        return $this->get_id();
    }

    /**
     * @param string $module
     *
     * @return $this
     */
    public function addModule($module)
    {
        $modules = $this->getModules();

        // Skip adding if provided path was added already
        if (!$module || in_array($module, $modules) ) {
            return $this;
        }

        $modules[] = $module;

        $this->setModules($modules);
        return $this;
    }

    /**
     * @return string[]
     */
    public function getModules()
    {
        return (array) $this->get('modules');
    }

    protected function setModules(array $modules)
    {
        $this->set('modules', $modules);
        return $this;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->get('hash');
    }

    public function setHash($value)
    {
        $this->set('hash', $value);
        return $this;
    }

    /**
     * @param string $hash
     *
     * @return PhpExceptionModelInterface|null
     */
    public function findByHash($hash)
    {
        $model = $this->model_factory()
            ->where('hash', '=', $hash)
            ->find();

        return $model->loaded() ? $model : null;
    }

    /**
     * @return int
     */
    public function getCounter()
    {
        return $this->loaded() ? (int) $this->getTotal() : 0;
    }

    /**
     * @return int
     */
    protected function getTotal()
    {
        return $this->get('total');
    }

    protected function setTotal($value)
    {
        $this->set('total', (int) $value);
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
    public function getMessage()
    {
        return $this->get('message');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMessage($value)
    {
        $this->set('message', $value);
        return $this;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function addPath($path)
    {
        $paths = $this->getPaths();

        // Skip adding if provided path was added already
        if (!$path || in_array($path, $paths) ) {
            return $this;
        }

        $paths[] = $path;

        $this->setPaths($paths);
        return $this;
    }

    /**
     * @return string[]
     */
    public function getPaths()
    {
        return (array) $this->get('paths');
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
    public function addUrl($url)
    {
        $urls = $this->getUrls();

        // Skip adding if provided path was added already
        if (!$url || in_array($url, $urls) ) {
            return $this;
        }

        $urls[] = $url;

        $this->setUrls($urls);
        return $this;
    }

    /**
     * @return string[]
     */
    public function getUrls()
    {
        return (array) $this->get('urls');
    }

    protected function setUrls(array $urls)
    {
        $this->set('urls', $urls);
        return $this;
    }

    /**
     * @param \DateTime|NULL $time
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $time)
    {
        $this->set_datetime_column_value('created_at', $time);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->get_datetime_column_value('created_at');
    }

    /**
     * Unix timestamp of last notification time
     *
     * @param \DateTime|NULL $time
     *
     * @return $this
     */
    public function setLastSeenAt(\DateTime $time)
    {
        $this->set_datetime_column_value('last_seen_at', $time);

        return $this;
    }

    /**
     * Unix timestamp of last notification time
     *
     * @return \DateTime|NULL
     */
    public function getLastSeenAt()
    {
        return $this->get_datetime_column_value('last_seen_at');
    }


    /**
     * Unix timestamp of last notification time
     *
     * @param \DateTime|NULL $time
     *
     * @return $this
     */
    public function setLastNotifiedAt(\DateTime $time)
    {
        $this->set_datetime_column_value('last_notified_at', $time);

        return $this;
    }

    /**
     * Unix timestamp of last notification time
     *
     * @return \DateTime|NULL
     */
    public function getLastNotifiedAt()
    {
        return $this->get_datetime_column_value('last_notified_at');
    }

    protected function setResolvedBy(UserInterface $user = null)
    {
        $this->set('resolved_by', $user ? $user->get_id() : null);
        return $this;
    }

    /**
     * @return UserInterface|null
     */
    public function getResolvedBy()
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
    public function markAsNew(UserInterface $user = null)
    {
        $this->setStatus(PhpExceptionModelInterface::STATE_NEW);
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
    public function markAsRepeated(UserInterface $user = null)
    {
        // Skip if exception was not resolved yet
        if (!$this->isResolved()) {
            return $this;
        }

        // Reset resolved_by
        $this->setResolvedBy(null);
        $this->setStatus(PhpExceptionModelInterface::STATE_REPEATED);
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
        $this->setStatus(PhpExceptionModelInterface::STATE_RESOLVED);
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
        $this->setStatus(PhpExceptionModelInterface::STATE_IGNORED);
        $this->addHistoryRecord($user);

        return $this;
    }

    /**
     * Returns TRUE if exception was resolved
     *
     * @return bool
     */
    public function isResolved()
    {
        return $this->getStatus() === PhpExceptionModelInterface::STATE_RESOLVED;
    }

    /**
     * Returns TRUE if current exception is in 'repeat' state
     *
     * @return bool
     */
    public function isRepeated()
    {
        return $this->getStatus() === PhpExceptionModelInterface::STATE_REPEATED;
    }

    /**
     * Returns TRUE if current exception is in 'new' state
     *
     * @return bool
     */
    public function isNew()
    {
        return !$this->getID() || $this->getStatus() === PhpExceptionModelInterface::STATE_NEW;
    }

    /**
     * Returns TRUE if current exception is in 'ignored' state
     *
     * @return bool
     */
    public function isIgnored()
    {
        return $this->getStatus() === PhpExceptionModelInterface::STATE_IGNORED;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->get('status');
    }

    /**
     * @param string $status
     *
     * @return PhpExceptionModelInterface
     */
    protected function setStatus($status)
    {
        $this->set('status', (string) $status);
        return $this;
    }

    /**
     * @return Model_PhpExceptionHistory
     */
    protected function getHistoryRelation()
    {
        return $this->get('history');
    }

    /**
     * @return PhpExceptionHistoryModelInterface[]
     */
    public function getHistoricalRecords()
    {
        return $this->getHistoryRelation()->get_all();
    }

    /**
     * Adds record to history
     *
     * @param UserInterface|null $user
     *
     * @return \BetaKiller\Error\PhpExceptionHistoryModelInterface
     * @internal param string $type What have been done
     */
    protected function addHistoryRecord(UserInterface $user = null)
    {
        // Get error ID for new records
        $this->save();

        return $this->phpExceptionHistoryModelFactory()
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
    public function delete()
    {
        // Delete historical records coz SQLite can not do it automatically
        foreach ($this->getHistoricalRecords() as $history) {
            $history->delete();
        }

        return parent::delete();
    }

    /**
     * @return $this
     */
    public function filterUnresolved()
    {
        return $this->where('resolved_by', 'IS', null);
    }

    /**
     * @return $this
     */
    public function filterResolved()
    {
        return $this->where('resolved_by', 'IS NOT', null);
    }

    /**
     * @param bool $desc
     *
     * @return $this
     */
    public function orderByCreatedAt($desc = true)
    {
        return $this->order_by('created_at', $desc ? 'desc' : 'asc');
    }

    /**
     * @return string
     */
    public function get_admin_url()
    {
        /** @var \BetaKiller\IFace\Admin\Error\PhpExceptionItem $iface */
        $iface = $this->iface_from_codename('Admin_Error_PhpExceptionItem');

        $params = $this->url_parameters_instance()->set(self::URL_PARAM, $this);

        return $iface->url($params);
    }
}
