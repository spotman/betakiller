<?php
namespace BetaKiller\Model;


interface AbstractEntityInterface extends HasLabelInterface
{
    /**
     * Returns string identifier for current entity (DB record ID, instance-related unique hash, etc)
     *
     * @return string
     */
    public function getID();

    /**
     * @return string
     */
    public function getModelName();
}
