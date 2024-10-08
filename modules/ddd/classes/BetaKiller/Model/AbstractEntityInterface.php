<?php
namespace BetaKiller\Model;

interface AbstractEntityInterface
{
    /**
     * Returns string identifier for current entity (DB record ID, instance-related unique hash, etc)
     *
     * @return string
     */
    public function getID(): string;

    /**
     * @return bool
     */
    public function hasID(): bool;

    /**
     * @return string
     */
    public static function getModelName(): string;
}
