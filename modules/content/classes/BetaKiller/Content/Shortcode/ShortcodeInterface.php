<?php
namespace BetaKiller\Content\Shortcode;

interface ShortcodeInterface
{
    public const CLASS_NS          = 'Shortcode';
    public const CLASS_SUFFIX      = 'Shortcode';

    public function getCodename(): string;

    /**
     * Returns HTML tag name
     *
     * @return string
     */
    public function getTagName(): string;

    /**
     * @return \BetaKiller\Content\Shortcode\Attribute\ShortcodeAttributeInterface[]
     */
    public function getAttributesDefinitions(): array;

    /**
     * @param array $values
     */
    public function setAttributes(array $values): void;

    /**
     * Empty attributes list
     */
    public function clearAttributes(): void;

    /**
     * @return array
     */
    public function getAttributes(): array;

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasAttribute(string $key): bool;

    /**
     * @param string $name
     *
     * @return null|string
     */
    public function getAttribute(string $name): ?string;

    /**
     * @param string      $key
     * @param null|string $value
     */
    public function setAttribute(string $key, ?string $value): void;

    /**
     * Returns true if current tag may have text content between opening and closing markers
     *
     * @return bool
     */
    public function mayHaveContent(): bool;

    /**
     * @return bool
     */
    public function hasContent(): bool;

    /**
     * @return null|string
     */
    public function getContent(): ?string;

    /**
     * @param string $value
     */
    public function setContent(string $value): void;

    /**
     * Returns data for rendering
     *
     * @return array
     */
    public function getWidgetData(): array;

    /**
     * @return string
     */
    public function getWysiwygPluginPreviewSrc(): string;

    /**
     * Returns string representation of current shortcode
     *
     * @return string
     */
    public function asHtml(): string;

    /**
     * Returns DOMText node with shortcode as a text
     *
     * @return \DOMText
     */
    public function asDomText(): \DOMText;

    /**
     * Validate attributes
     */
    public function validateAttributes(): void;
}
