<?php
namespace BetaKiller\Content\Shortcode\Attribute;


class BooleanAttribute extends DiscreteValuesAttribute
{
    public const TRUE = 'true';
    public const FALSE = 'false';

    /**
     * DiscreteValuesAttribute constructor.
     *
     * @param string    $name
     * @param bool|null $isOptional
     */
    public function __construct(string $name, ?bool $isOptional = null)
    {
        parent::__construct($name, [self::TRUE, self::FALSE], $isOptional);
    }
}
