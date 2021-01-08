<?php
namespace BetaKiller\Model;

use BetaKiller\Url\Parameter\UtmCampaignUrlParameter;
use BetaKiller\Url\Parameter\UtmContentUrlParameter;
use BetaKiller\Url\Parameter\UtmMediumUrlParameter;
use BetaKiller\Url\Parameter\UtmSourceUrlParameter;
use BetaKiller\Url\Parameter\UtmTermUrlParameter;

class HitMarker extends \ORM implements HitMarkerInterface
{
    public const COL_SOURCE   = 'source';
    public const COL_MEDIUM   = 'medium';
    public const COL_CAMPAIGN = 'campaign';
    public const COL_TERM     = 'term';
    public const COL_CONTENT  = 'content';

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     * @throws \Exception
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
        $this->set(self::COL_SOURCE, $value);

        return $this;
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\HitMarkerInterface
     */
    public function setMedium(string $value): HitMarkerInterface
    {
        $this->set(self::COL_MEDIUM, $value);

        return $this;
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\HitMarkerInterface
     */
    public function setCampaign(string $value): HitMarkerInterface
    {
        $this->set(self::COL_CAMPAIGN, $value);

        return $this;
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\HitMarkerInterface
     */
    public function setTerm(string $value): HitMarkerInterface
    {
        $this->set(self::COL_TERM, $value);

        return $this;
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\HitMarkerInterface
     */
    public function setContent(string $value): HitMarkerInterface
    {
        $this->set(self::COL_CONTENT, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSource(): ?string
    {
        return (string)$this->get(self::COL_SOURCE);
    }

    /**
     * @inheritDoc
     */
    public function getMedium(): ?string
    {
        return $this->get(self::COL_MEDIUM);
    }

    /**
     * @inheritDoc
     */
    public function getCampaign(): ?string
    {
        return $this->get(self::COL_CAMPAIGN);
    }

    /**
     * @inheritDoc
     */
    public function getTerm(): ?string
    {
        return (string)$this->get(self::COL_TERM);
    }

    /**
     * @inheritDoc
     */
    public function getContent(): ?string
    {
        return (string)$this->get(self::COL_CONTENT);
    }

    /**
     * @return string[]
     */
    public function asQueryArray(): array
    {
        return \array_filter([
            UtmSourceUrlParameter::QUERY_KEY   => $this->getSource(),
            UtmMediumUrlParameter::QUERY_KEY   => $this->getMedium(),
            UtmCampaignUrlParameter::QUERY_KEY => $this->getCampaign(),
            UtmContentUrlParameter::QUERY_KEY  => $this->getContent(),
            UtmTermUrlParameter::QUERY_KEY     => $this->getTerm(),
        ]);
    }
}
