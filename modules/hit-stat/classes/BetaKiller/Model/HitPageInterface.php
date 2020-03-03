<?php
namespace BetaKiller\Model;

use Psr\Http\Message\UriInterface;

interface HitPageInterface extends AbstractEntityInterface
{
    public function setDomain(HitDomain $domain): HitPageInterface;

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
