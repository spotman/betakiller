<?php
namespace BetaKiller\Model;

class RefLog extends \ORM
{
    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function _initialize(): void
    {
        $this->_table_name = 'ref_log';

        parent::_initialize();
    }

    public function setSourceUrl(string $url): RefLog
    {
        $this->set('source_url', $url);
        return $this;
    }

    public function setTargetUrl(string $url): RefLog
    {
        $this->set('target_url', $url);
        return $this;
    }

    public function setIP(string $ip): RefLog
    {
        $this->set('ip', $ip);
        return $this;
    }

    public function setTimestamp(\DateTimeImmutable $dateTime): RefLog
    {
        $this->set_datetime_column_value('created_at', $dateTime);
        return $this;
    }

    public function markAsProcessed(): void
    {
        $this->set('processed', 1);
    }
}
