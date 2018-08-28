<?php
defined('SYSPATH') OR die('No direct script access.');


class Config_Group extends Kohana_Config_Group
{
    /**
     * @return array
     */
    public function asArray(): array
    {
        return $this->as_array();
    }
}
