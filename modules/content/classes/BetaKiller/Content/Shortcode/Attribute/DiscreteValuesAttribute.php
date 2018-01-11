<?php
namespace BetaKiller\Content\Shortcode\Attribute;


class DiscreteValuesAttribute extends RegexAttribute
{
    /**
     * DiscreteValuesAttribute constructor.
     *
     * @param string    $name
     * @param array     $values
     * @param bool|null $isOptional
     */
    public function __construct(string $name, array $values, bool $isOptional = null)
    {
        parent::__construct($name, '/('.implode('|', $values).')/', $isOptional);
    }
}
