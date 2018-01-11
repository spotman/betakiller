<?php
namespace BetaKiller\Content\Shortcode\Attribute;


abstract class AbstractShortcodeAttribute implements ShortcodeAttributeInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $isOptional;

    /**
     * AbstractShortcodeAttribute constructor.
     *
     * @param string    $name
     * @param bool|null $isOptional
     */
    public function __construct(string $name, ?bool $isOptional = null)
    {
        $this->name = $name;
        $this->isOptional = $isOptional ?? false;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isOptional(): bool
    {
        return $this->isOptional;
    }
}
