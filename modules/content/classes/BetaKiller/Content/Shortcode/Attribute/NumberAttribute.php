<?php
namespace BetaKiller\Content\Shortcode\Attribute;


class NumberAttribute extends AbstractRegexAttribute
{
    public function __construct(string $name)
    {
        parent::__construct($name, '/[0-9]+/');
    }

    /**
     * Returns attribute type
     *
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_NUMBER;
    }
}
