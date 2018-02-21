<?php
namespace BetaKiller\Content\Shortcode\Attribute;


class BooleanAttribute extends SwitchAttribute
{
    public const VALUE_TRUE = 'true';
    public const VALUE_FALSE = 'false';

    /**
     * SwitchAttribute constructor.
     *
     * @param string    $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name, [self::VALUE_TRUE, self::VALUE_FALSE]);
    }

    /**
     * Returns attribute type
     *
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_BOOLEAN;
    }

    public function optionalFalse(): BooleanAttribute
    {
        $this->optional(self::VALUE_FALSE);
        return $this;
    }

    public function optionalTrue(): BooleanAttribute
    {
        $this->optional(self::VALUE_TRUE);
        return $this;
    }
}
