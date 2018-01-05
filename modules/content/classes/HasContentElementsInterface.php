<?php

interface HasContentElementsInterface
{
    /**
     * Returns entity ID (content_entities.id value)
     *
     * @return int
     */
    public function getContentEntityID(): int;
}
