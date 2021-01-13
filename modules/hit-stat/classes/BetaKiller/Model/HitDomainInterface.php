<?php
namespace BetaKiller\Model;

interface HitDomainInterface extends AbstractEntityInterface
{
    /**
     * @param string $name
     *
     * @return \BetaKiller\Model\HitDomainInterface
     */
    public function setName(string $name): HitDomainInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     *
     */
    public function markAsInternal(): void;

    /**
     *
     */
    public function markAsExternal(): void;

    /**
     *
     */
    public function markAsIgnored(): void;

    /**
     *
     */
    public function markAsActive(): void;

    /**
     * @return bool
     */
    public function isIgnored(): bool;
}
