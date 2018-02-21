<?php
namespace BetaKiller\Content\Shortcode\Attribute;


/**
 * Class CommaSeparatedIDsAttribute
 *
 * @package BetaKiller\Content\Shortcode\Attribute
 * @deprecated
 */
class CommaSeparatedIDsAttribute extends AbstractRegexAttribute
{
    /**
     * AbstractRegexAttribute constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name, '/[0-9,]+/');
    }

    /**
     * Returns attribute type
     *
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_STRING;
    }
}
