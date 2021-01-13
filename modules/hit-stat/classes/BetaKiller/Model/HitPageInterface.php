<?php
namespace BetaKiller\Model;

use Psr\Http\Message\UriInterface;
use Spotman\Api\ApiResponseItemInterface;

interface HitPageInterface extends AbstractEntityInterface, ApiResponseItemInterface
{
    public const API_KEY_ID       = 'id';
    public const API_KEY_FULL_URL = 'full_url';

    public function setDomain(HitDomainInterface $domain): HitPageInterface;

    public function setUri(string $url): HitPageInterface;

    public function incrementHits(): HitPageInterface;

    public function setHits(int $value): HitPageInterface;

    public function getUri(): string;

    public function getHits(): int;

    public function isIgnored(): bool;

    public function markAsIgnored(): HitPageInterface;

    public function markAsMissing(): HitPageInterface;

    public function markAsOk(): HitPageInterface;

    public function isMissing(): bool;

    public function setRedirect(HitPageRedirectInterface $redirect): HitPageInterface;

    public function getRedirect(): ?HitPageRedirectInterface;

    public function setLastSeenAt(\DateTimeImmutable $dateTime): HitPageInterface;

    public function setFirstSeenAt(\DateTimeImmutable $dateTime): HitPageInterface;

    public function getLastSeenAt(): \DateTimeImmutable;

    public function getFirstSeenAt(): \DateTimeImmutable;

    public function getFullUrl(): UriInterface;
}
