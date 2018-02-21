<?php
namespace BetaKiller\Content\Shortcode\Attribute;


class NumberAttribute extends RegexAttribute
{
    public function __construct(string $name)
    {
        parent::__construct($name, '/[0-9]+/');
    }
}
