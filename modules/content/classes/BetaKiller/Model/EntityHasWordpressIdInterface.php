<?php
namespace BetaKiller\Model;

interface EntityHasWordpressIdInterface
{
    /**
     * @param int $value
     */
    public function setWpId(int $value): void;

    /**
     * @return int|null
     */
    public function getWpId(): ?int;
}
