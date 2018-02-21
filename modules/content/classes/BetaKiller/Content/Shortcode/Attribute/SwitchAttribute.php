<?php
namespace BetaKiller\Content\Shortcode\Attribute;


class SwitchAttribute extends AbstractRegexAttribute
{
    /**
     * SwitchAttribute constructor.
     *
     * @param string $name
     * @param array  $allowedValues
     */
    public function __construct(string $name, array $allowedValues)
    {
        parent::__construct($name, '/('.implode('|', $allowedValues).')/');

        $this->allowedValues = $allowedValues;
    }

    /**
     * Returns attribute type
     *
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_SWITCH;
    }
}
