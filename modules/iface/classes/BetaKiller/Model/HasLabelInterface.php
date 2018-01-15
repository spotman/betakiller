<?php
namespace BetaKiller\Model;

interface HasLabelInterface
{
    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @param string $value
     */
    public function setLabel(string $value): void;
}
