<?php
namespace BetaKiller\Content\Shortcode\Attribute;


class DiscreteValuesAttribute extends RegexAttribute
{
    /**
     * DiscreteValuesAttribute constructor.
     *
     * @param string    $name
     * @param array     $values
     */
    public function __construct(string $name, array $values)
    {
        parent::__construct($name, '/('.implode('|', $values).')/');
    }
}
