<?php
namespace BetaKiller\Model;

class HitLink extends \ORM
{
    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     * @throws \Exception
     */
    protected function configure(): void
    {
        $this->_table_name = 'stat_hit_links';

        $this->belongs_to([
            'source' => [
                'model'       => 'HitPage',
                'foreign_key' => 'source_id',
            ],
            'target' => [
                'model'       => 'HitPage',
                'foreign_key' => 'target_id',
            ],
        ]);

        $this->load_with(['source', 'target']);
    }

    public function setSource(?HitPageInterface $source): HitLink
    {
        $this->set('source', $source);

        return $this;
    }

    public function setTarget(HitPageInterface $target): HitLink
    {
        $this->set('target', $target);

        return $this;
    }

    public function incrementClicks(): void
    {
        $this->setClicks($this->getClicks() + 1);
    }

    public function setClicks(int $value): HitLink
    {
        $this->set('clicks', $value);

        return $this;
    }

    public function setLastSeenAt(\DateTimeImmutable $dateTime): HitLink
    {
        $this->set_datetime_column_value('last_seen_at', $dateTime);

        return $this;
    }

    public function setFirstSeenAt(\DateTimeImmutable $dateTime): HitLink
    {
        $this->set_datetime_column_value('first_seen_at', $dateTime);

        return $this;
    }

    public function getSource(): HitPageInterface
    {
        return $this->get('source');
    }

    public function getTarget(): HitPageInterface
    {
        return $this->get('target');
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
