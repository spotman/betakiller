<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\IFace\AbstractIFace;

class IFace_Auth_Password_Reset extends AbstractIFace
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData(): array
    {
        return [];
    }
}
