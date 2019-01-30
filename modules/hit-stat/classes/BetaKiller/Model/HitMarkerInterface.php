<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface HitMarkerInterface
{
    public const UTM_QUERY_TERM     = 'utm_term';
    public const UTM_QUERY_MEDIUM   = 'utm_medium';
    public const UTM_QUERY_SOURCE   = 'utm_source';
    public const UTM_QUERY_CONTENT  = 'utm_content';
    public const UTM_QUERY_CAMPAIGN = 'utm_campaign';
    public const UTM_QUERY_KEYS     = [
        HitMarkerInterface::UTM_QUERY_SOURCE,
        HitMarkerInterface::UTM_QUERY_MEDIUM,
        HitMarkerInterface::UTM_QUERY_CAMPAIGN,
        HitMarkerInterface::UTM_QUERY_CONTENT,
        HitMarkerInterface::UTM_QUERY_TERM,
    ];

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\HitMarkerInterface
     */
    public function setSource(string $value): HitMarkerInterface;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\HitMarkerInterface
     */
    public function setMedium(string $value): HitMarkerInterface;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\HitMarkerInterface
     */
    public function setCampaign(string $value): HitMarkerInterface;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\HitMarkerInterface
     */
    public function setTerm(string $value): HitMarkerInterface;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\HitMarkerInterface
     */
    public function setContent(string $value): HitMarkerInterface;

    /**
     * @return string
     */
    public function getSource(): string;

    /**
     * @return string
     */
    public function getMedium(): string;

    /**
     * @return string
     */
    public function getCampaign(): string;

    /**
     * @return string|null
     */
    public function getTerm(): ?string;

    /**
     * @return string|null
     */
    public function getContent(): ?string;

    /**
     * @return string[]
     */
    public function asQueryArray(): array;
}
