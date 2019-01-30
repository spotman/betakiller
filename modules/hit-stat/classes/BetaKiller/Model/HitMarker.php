<?php
namespace BetaKiller\Model;

class HitMarker extends \ORM implements HitMarkerInterface
{
    public const FIELD_SOURCE   = 'source';
    public const FIELD_MEDIUM   = 'medium';
    public const FIELD_CAMPAIGN = 'campaign';
    public const FIELD_TERM     = 'term';
    public const FIELD_CONTENT  = 'content';

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function configure(): void
    {
        $this->_table_name = 'stat_hit_markers';
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\HitMarkerInterface
     */
    public function setSource(string $value): HitMarkerInterface
    {
        $this->set(self::FIELD_SOURCE, $value);

        return $this;
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\HitMarkerInterface
     */
    public function setMedium(string $value): HitMarkerInterface
    {
        $this->set(self::FIELD_MEDIUM, $value);

        return $this;
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\HitMarkerInterface
     */
    public function setCampaign(string $value): HitMarkerInterface
    {
        $this->set(self::FIELD_CAMPAIGN, $value);

        return $this;
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\HitMarkerInterface
     */
    public function setTerm(string $value): HitMarkerInterface
    {
        $this->set(self::FIELD_TERM, $value);

        return $this;
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\HitMarkerInterface
     */
    public function setContent(string $value): HitMarkerInterface
    {
        $this->set(self::FIELD_CONTENT, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return (string)$this->get(self::FIELD_SOURCE);
    }

    /**
     * @return string
     */
    public function getMedium(): string
    {
        return (string)$this->get(self::FIELD_MEDIUM);
    }

    /**
     * @return string
     */
    public function getCampaign(): string
    {
        return (string)$this->get(self::FIELD_CAMPAIGN);
    }

    /**
     * @return string|null
     */
    public function getTerm(): ?string
    {
        return (string)$this->get(self::FIELD_TERM);
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return (string)$this->get(self::FIELD_CONTENT);
    }
}
