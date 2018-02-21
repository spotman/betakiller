<?php
namespace BetaKiller\Content\Shortcode\Attribute;


abstract class AbstractShortcodeAttribute implements ShortcodeAttributeInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @var bool
     */
    private $isOptional = false;

    /**
     * @var bool
     */
    private $isHidden = false;

    /**
     * @var string[]
     */
    private $dependencies = [];

    /**
     * @var string[]
     */
    protected $allowedValues = [];

    /**
     * AbstractShortcodeAttribute constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Marks attribute as optional and sets default value
     *
     * @param string|null $defaultValue
     *
     * @return \BetaKiller\Content\Shortcode\Attribute\ShortcodeAttributeInterface
     */
    public function optional(string $defaultValue = null): ShortcodeAttributeInterface
    {
        $this->defaultValue = $defaultValue;
        $this->isOptional   = true;

        return $this;
    }

    /**
     * Returns true if current attribute was marked as optional
     *
     * @return bool
     */
    public function isOptional(): bool
    {
        return $this->isOptional;
    }

    /**
     * Mark attribute as hidden (allowed in shortcode but not editable in UI)
     *
     * @return \BetaKiller\Content\Shortcode\Attribute\ShortcodeAttributeInterface
     */
    public function hidden(): ShortcodeAttributeInterface
    {
        $this->isHidden = true;

        return $this;
    }

    /**
     * Returns true if current attribute is hidden (allowed in shortcode but not editable in UI)
     *
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->isHidden;
    }

    /**
     * Returns default attribute value
     *
     * @return null|string
     */
    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    /**
     * Returns array of allowed values if defined (empty array means any value is allowed)
     *
     * @return string[]
     */
    public function getAllowedValues(): array
    {
        return $this->allowedValues;
    }

    /**
     * Marks current attribute as dependent on another one`s value
     *
     * @param string      $name
     * @param string|null $value
     *
     * @return \BetaKiller\Content\Shortcode\Attribute\ShortcodeAttributeInterface
     */
    public function dependsOn(string $name, ?string $value): ShortcodeAttributeInterface
    {
        $this->dependencies[$name] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Returns true if current attribute has dependencies
     *
     * @return bool
     */
    public function hasDependencies(): bool
    {
        return \count($this->dependencies) > 0;
    }
}
