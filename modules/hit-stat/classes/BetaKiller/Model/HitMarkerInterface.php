<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface HitMarkerInterface
{
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
}
