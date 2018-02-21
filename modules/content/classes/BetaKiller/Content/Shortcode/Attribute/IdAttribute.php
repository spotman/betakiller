<?php
namespace BetaKiller\Content\Shortcode\Attribute;


class IdAttribute extends NumberAttribute
{
    public function __construct()
    {
        parent::__construct('id');
    }
}
