<?php
namespace BetaKiller\Content\Shortcode\Attribute;


interface ShortcodeAttributeInterface
{
    /**
     * Returns attribute name
     *
     * @return string
     */
    public function getName(): string;

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
     * Returns default attribute value
     *
     * @return null|string
     */
    public function getDefaultValue(): ?string;

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
     * @return array
     */
    public function getDependencies(): array;

    /**
     * Returns true if current attribute has dependencies
     *
     * @return bool
     */
    public function hasDependencies(): bool;
}
