<?php
namespace BetaKiller\Content\Shortcode\Attribute;


class CommaSeparatedIDsAttribute extends RegexAttribute
{
    /**
     * RegexAttribute constructor.
     *
     * @param string    $name
     * @param bool|null $isOptional
     */
    public function __construct(string $name, ?bool $isOptional = null)
    {
        parent::__construct($name, '/[0-9,]+/', $isOptional);
    }
}
