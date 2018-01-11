<?php
namespace BetaKiller\Content\Shortcode\Attribute;


class NumberAttribute extends RegexAttribute
{
    public function __construct(string $name, bool $isOptional = null)
    {
        parent::__construct($name, '/[0-9]+/', $isOptional);
    }
}
