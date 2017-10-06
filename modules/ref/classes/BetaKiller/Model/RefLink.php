<?php
namespace BetaKiller\Model;

class RefLink extends \ORM
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
        $this->_table_name = 'ref_links';

        $this->belongs_to([
            'source' => [
                'model'       => 'RefPage',
                'foreign_key' => 'source_id',
            ],
            'target' => [
                'model'       => 'RefPage',
                'foreign_key' => 'target_id',
            ],
        ]);

        $this->load_with(['source', 'target']);

        parent::_initialize();
    }

    public function setSource(?RefPage $source): RefLink
    {
        $this->set('source', $source);
        return $this;
    }

    public function setTarget(RefPage $target): RefLink
    {
        $this->set('target', $target);
        return $this;
    }

    public function setUrl(string $url): RefLink
    {
        $this->set('uri', $url);

        return $this;
    }

    public function incrementClicks(): void
    {
        $this->setClicks($this->getClicks() + 1);
    }

    public function setClicks(int $value): RefLink
    {
        $this->set('clicks', $value);

        return $this;
    }

    public function setLastSeenAt(\DateTimeImmutable $dateTime): RefLink
    {
        $this->set_datetime_column_value('last_seen_at', $dateTime);
        return $this;
    }

    public function setFirstSeenAt(\DateTimeImmutable $dateTime): RefLink
    {
        $this->set_datetime_column_value('first_seen_at', $dateTime);
        return $this;
    }

    public function getSource(): RefPage
    {
        return $this->get('source');
    }

    public function getTarget(): RefPage
    {
        return $this->get('target');
    }

    public function getUrl(): string
    {
        return $this->get('url');
    }

    public function getClicks(): int
    {
        return (int)$this->get('clicks');
    }

    public function getLastSeenAt(): ?\DateTimeImmutable
    {
        return $this->get_datetime_column_value('last_seen_at');
    }

    public function getFirstSeenAt(): ?\DateTimeImmutable
    {
        return $this->get_datetime_column_value('first_seen_at');
    }
}
