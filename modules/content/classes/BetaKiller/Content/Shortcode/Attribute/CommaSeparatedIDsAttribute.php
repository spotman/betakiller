<?php
namespace BetaKiller\Content\Shortcode\Attribute;


class CommaSeparatedIDsAttribute extends RegexAttribute
{
    /**
     * RegexAttribute constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name, '/[0-9,]+/');
    }
}
