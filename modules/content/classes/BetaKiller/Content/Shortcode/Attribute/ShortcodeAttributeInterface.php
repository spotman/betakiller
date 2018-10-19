<?php
namespace BetaKiller\Content\Shortcode\Attribute;


interface ShortcodeAttributeInterface extends \JsonSerializable
{
    /**
     * Type for simple string-based attributes like "title" or "class"
     */
    public const TYPE_STRING = 'string';

    /**
     * Type for number-based attributes like "width" or "height"
     */
    public const TYPE_NUMBER = 'number';

    /**
     * Type for attributes having several discrete values like "layout"
     */
    public const TYPE_SWITCH = 'switch';

    /**
     * Type for boolean-like attributes having only "true" and "false" values
     */
    public const TYPE_BOOLEAN = 'boolean';

    /**
     * Type for selecting [other] shortcode item ID
     */
    public const TYPE_ITEM = 'item';

    /**
     * Returns attribute name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns attribute type
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Returns true if provided value is allowed
     *
     * @param string $value
     *
     * @return bool
     */
    public function isValueAvailable(string $value): bool;

    /**
     * Marks attribute as optional and sets default value
     *
     * @param string|null $defaultValue
     *
     * @return \BetaKiller\Content\Shortcode\Attribute\ShortcodeAttributeInterface
     */
    public function optional(string $defaultValue = null): ShortcodeAttributeInterface;

    /**
     * Returns true if current attribute was marked as optional
     *
     * @return bool
     */
    public function isOptional(): bool;

    /**
     * Mark attribute as hidden (allowed in shortcode but not editable in UI)
     *
     * @return \BetaKiller\Content\Shortcode\Attribute\ShortcodeAttributeInterface
     */
    public function hidden(): ShortcodeAttributeInterface;

    /**
     * Returns true if current attribute is hidden (allowed in shortcode but not editable in UI)
     *
     * @return bool
     */
    public function isHidden(): bool;

    /**
     * Returns default attribute value
     *
     * @return null|string
     */
    public function getDefaultValue(): ?string;

    /**
     * Returns array of allowed values if defined (empty array means any value is allowed)
     *
     * @return string[]
     */
    public function getAllowedValues(): array;

    /**
     * Marks current attribute as dependent on another one`s value
     *
     * @param string      $name
     * @param string|null $value
     *
     * @return \BetaKiller\Content\Shortcode\Attribute\ShortcodeAttributeInterface
     */
    public function dependsOn(string $name, ?string $value): ShortcodeAttributeInterface;

    /**
     * Returns name => value pairs of attributes and their values
     *
     * @return string[]
     */
    public function getDependencies(): array;

    /**
     * Returns true if current attribute has dependencies
     *
     * @return bool
     */
    public function hasDependencies(): bool;

    /**
     * Returns i18n key for attribute`s label
     *
     * @return string
     */
    public function getLabelI18nKey(): string;
}
