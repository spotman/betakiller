<?php
namespace BetaKiller\Content\Shortcode\Attribute;


class IdAttribute extends NumberAttribute
{
    public function __construct(bool $isOptional = null)
    {
        parent::__construct('id', $isOptional);
    }
}
