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
     */
    public function __construct(string $name)
    {
        parent::__construct($name, [self::TRUE, self::FALSE]);
    }

    public function optionalFalse(): BooleanAttribute
    {
        $this->optional(self::FALSE);
        return $this;
    }

    public function optionalTrue(): BooleanAttribute
    {
        $this->optional(self::TRUE);
        return $this;
    }
}
