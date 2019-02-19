<?php
namespace BetaKiller\Model;

use Psr\Http\Message\UriInterface;

interface HitInterface extends AbstractEntityInterface
{
    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Model\HitInterface
     */
    public function bindToUser(UserInterface $user): HitInterface;

    /**
     * @param \BetaKiller\Model\HitPage|null $value
     *
     * @return \BetaKiller\Model\HitInterface
     */
    public function setSourcePage(HitPage $value): HitInterface;

    /**
     * @param \BetaKiller\Model\HitPage $value
     *
     * @return \BetaKiller\Model\HitInterface
     */
    public function setTargetPage(HitPage $value): HitInterface;

    /**
     * @param string $ip
     *
     * @return \BetaKiller\Model\HitInterface
     */
    public function setIP(string $ip): HitInterface;

    /**
     * @param \BetaKiller\Model\HitMarkerInterface $value
     *
     * @return \BetaKiller\Model\HitInterface
     */
    public function setTargetMarker(HitMarkerInterface $value): HitInterface;

    /**
     * @param \DateTimeImmutable $dateTime
     *
     * @return \BetaKiller\Model\HitInterface
     */
    public function setTimestamp(\DateTimeImmutable $dateTime): HitInterface;

    /**
     * @return bool
     */
    public function hasSourcePage(): bool;

    /**
     * @return \BetaKiller\Model\HitPage
     */
    public function getSourcePage(): HitPage;

    /**
     * @return \BetaKiller\Model\HitPage
     */
    public function getTargetPage(): HitPage;

    /**
     * @return \BetaKiller\Model\HitMarkerInterface
     */
    public function getTargetMarker(): HitMarkerInterface;

    /**
     * @return bool
     */
    public function hasTargetMarker(): bool;

    /**
     * @return string
     */
    public function getIP(): string;

    /**
     * @return \DateTimeImmutable
     */
    public function getTimestamp(): \DateTimeImmutable;

    /**
     * @return \Psr\Http\Message\UriInterface
     */
    public function getFullTargetUrl(): UriInterface;

    /**
     * @return bool
     */
    public function isProcessed(): bool;

    /**
     * @return \BetaKiller\Model\HitInterface
     */
    public function markAsProcessed(): HitInterface;
}
