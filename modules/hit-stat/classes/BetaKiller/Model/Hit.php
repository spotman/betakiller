<?php
namespace BetaKiller\Model;

class Hit extends \ORM implements HitInterface
{
    private const FIELD_SOURCE_ID = 'source_id';
    private const FIELD_TARGET_ID = 'target_id';
    private const FIELD_MARKER_ID = 'marker_id';

    private const RELATION_SOURCE = 'source';
    private const RELATION_TARGET = 'target';
    private const RELATION_MARKER = 'marker';

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->_table_name = 'stat_hits';

        $this->belongs_to([
            self::RELATION_SOURCE => [
                'model'       => 'HitPage',
                'foreign_key' => self::FIELD_SOURCE_ID,
            ],

            self::RELATION_TARGET => [
                'model'       => 'HitPage',
                'foreign_key' => self::FIELD_TARGET_ID,
            ],

            self::RELATION_MARKER => [
                'model'       => 'HitMarker',
                'foreign_key' => self::FIELD_MARKER_ID,
            ],
        ]);

        $this->load_with([
            self::RELATION_SOURCE,
            self::RELATION_TARGET,
            self::RELATION_MARKER,
        ]);
    }

    /**
     * @param \BetaKiller\Model\HitPage|null $value
     *
     * @return \BetaKiller\Model\HitInterface
     */
    public function setSourcePage(HitPage $value): HitInterface
    {
        $this->set(self::RELATION_SOURCE, $value);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\HitPage $value
     *
     * @return \BetaKiller\Model\HitInterface
     */
    public function setTargetPage(HitPage $value): HitInterface
    {
        $this->set(self::RELATION_TARGET, $value);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\HitMarkerInterface $value
     *
     * @return \BetaKiller\Model\HitInterface
     */
    public function setTargetMarker(HitMarkerInterface $value): HitInterface
    {
        $this->set(self::RELATION_MARKER, $value);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSource(): bool
    {
        return (bool)$this->get(self::FIELD_SOURCE_ID);
    }

    /**
     * @return \BetaKiller\Model\HitPage
     */
    public function getSource(): HitPage
    {
        return $this->getRelatedEntity(self::RELATION_SOURCE);
    }

    /**
     * @return \BetaKiller\Model\HitPage
     */
    public function getTarget(): HitPage
    {
        return $this->getRelatedEntity(self::RELATION_TARGET);
    }

    /**
     * @return \BetaKiller\Model\HitMarkerInterface
     */
    public function getTargetMarker(): HitMarkerInterface
    {
        return $this->getRelatedEntity(self::RELATION_MARKER);
    }

    /**
     * @return bool
     */
    public function hasTargetMarker(): bool
    {
        return (bool)$this->get(self::FIELD_MARKER_ID);
    }

    public function setIP(string $ip): HitInterface
    {
        $this->set('ip', $ip);

        return $this;
    }

    public function setTimestamp(\DateTimeImmutable $dateTime): HitInterface
    {
        $this->set_datetime_column_value('created_at', $dateTime);

        return $this;
    }

    /**
     * @throws \Kohana_Exception
     */
    public function markAsProcessed(): void
    {
        $this->set('processed', 1);
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
