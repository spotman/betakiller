<?php
namespace BetaKiller\Model;

class RefHit extends \ORM
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
        $this->_table_name = 'ref_hits';

        parent::_initialize();
    }

    public function setSourceUrl(?string $url): RefHit
    {
        $this->set('source_url', $url);
        return $this;
    }

    public function setTargetUrl(string $url): RefHit
    {
        $this->set('target_url', $url);
        return $this;
    }

    public function setIP(string $ip): RefHit
    {
        $this->set('ip', $ip);
        return $this;
    }

    public function setTimestamp(\DateTimeImmutable $dateTime): RefHit
    {
        $this->set_datetime_column_value('created_at', $dateTime);
        return $this;
    }

    public function markAsProcessed(): void
    {
        $this->set('processed', 1);
    }

    public function getSourceUrl(): ?string
    {
        return $this->get('source_url');
    }

    public function getTargetUrl(): string
    {
        return $this->get('target_url');
    }

    public function getIP(): string
    {
        return $this->get('ip');
    }

    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->get_datetime_column_value('created_at');
    }
}
