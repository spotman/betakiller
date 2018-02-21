<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Content\Shortcode\Attribute\ShortcodeAttributeInterface;

abstract class AbstractShortcode implements ShortcodeInterface
{
    /**
     * @var \BetaKiller\Content\Shortcode\ShortcodeEntityInterface
     */
    private $entity;

    /**
     * @var \BetaKiller\Content\Shortcode\Attribute\ShortcodeAttributeInterface[]
     */
    private $definitions;

    /**
     * @var array Shortcode tag values
     */
    private $attributes = [];

    /**
     * @var string
     */
    private $content;

    /**
     * @return string
     */
    public static function codename(): string
    {
        $className = static::class;
        $pos       = strrpos($className, '\\');
        $baseName  = substr($className, $pos + 1);

        return str_replace(self::CLASS_SUFFIX, '', $baseName);
    }

    /**
     * AbstractShortcode constructor.
     *
     * @param \BetaKiller\Content\Shortcode\ShortcodeEntityInterface $entity
     */
    public function __construct(ShortcodeEntityInterface $entity)
    {
        $this->entity = $entity;

        foreach ($this->getAttributesDefinitions() as $item) {
            $name                     = $item->getName();
            $this->definitions[$name] = $item;
        }
    }

    /**
     * Returns HTML tag name
     *
     * @return string
     */
    public function getTagName(): string
    {
        return $this->entity->getTagName();
    }

    /**
     * @return string
     */
    public function getCodename(): string
    {
        return $this->entity->getCodename();
    }

    /**
     * @param array $values
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function setAttributes(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }

    /**
     * @param string      $name
     * @param null|string $value
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function setAttribute(string $name, ?string $value): void
    {
        $this->checkAttributeIsAvailable($name);
        $this->checkAttributeIsValueAvailable($name, $value);

        $this->attributes[$name] = $value;
    }

    /**
     * Returns true if attribute is available for current tag
     *
     * @param string $name
     *
     * @return bool
     */
    private function isAttributeAvailable(string $name): bool
    {
        // Definition exists => attribute is available
        return $this->hasAttributeDefinition($name);
    }

    private function hasAttributeDefinition(string $name): bool
    {
        return isset($this->definitions[$name]);
    }

    /**
     * @param string $name
     *
     * @return \BetaKiller\Content\Shortcode\Attribute\ShortcodeAttributeInterface
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    private function getAttributeDefinition(string $name): ShortcodeAttributeInterface
    {
        if (!$this->hasAttributeDefinition($name)) {
            throw new ShortcodeException('Attribute [:name] definition is missing for [:tag] tag', [
                ':name' => $name,
                ':tag'  => $this->getTagName(),
            ]);
        }

        return $this->definitions[$name];
    }

    /**
     * Check for available attribute value
     *
     * @param string $name
     * @param string $value
     *
     * @return bool
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    private function isAttributeValueAvailable(string $name, ?string $value): bool
    {
        $definition = $this->getAttributeDefinition($name);

        // Required fields must not have an empty value, optional ones may
        if (!$value) {
            return $definition->isOptional();
        }

        return $definition->isValueAvailable($value);
    }

    /**
     * @param string      $name
     * @param null|string $value
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    private function checkAttributeIsValueAvailable(string $name, ?string $value): void
    {
        if (!$this->isAttributeValueAvailable($name, $value)) {
            throw new ShortcodeException('Attribute [:name] value [:value] is not allowed for shortcode [:tag]', [
                ':name'  => $name,
                ':value' => $value,
                ':tag'   => $this->getTagName(),
            ]);
        }
    }

    /**
     * Empty attributes list
     */
    public function clearAttributes(): void
    {
        $this->attributes = [];
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     *
     * @return null|string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getAttribute(string $name): ?string
    {
        $this->checkAttributeIsAvailable($name);

        if (!isset($this->attributes[$name]) && $this->isRequiredAttribute($name)) {
            throw new ShortcodeException('Missing :name attribute', [
                ':name' => $name,
            ]);
        }

        $definition = $this->getAttributeDefinition($name);

        return $this->attributes[$name] ?? $definition->getDefaultValue();
    }

    /**
     * @param string $name
     *
     * @return bool
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    private function isRequiredAttribute(string $name): bool
    {
        $definition = $this->getAttributeDefinition($name);

        // Non-actual attributes are not required
        if (!$this->isActualAttribute($definition)) {
            return false;
        }

        // Non optional => required
        return !$definition->isOptional();
    }

    /**
     * @param \BetaKiller\Content\Shortcode\Attribute\ShortcodeAttributeInterface $definition
     *
     * @return bool
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    private function isActualAttribute(ShortcodeAttributeInterface $definition): bool
    {
        // Attribute without dependencies => always actual
        if (!$definition->hasDependencies()) {
            return true;
        }

        foreach ($definition->getDependencies() as $targetName => $targetValue) {
            $targetDefinition = $this->getAttributeDefinition($targetName);

            // Non-actual parent makes all children non-actual too
            if (!$this->isActualAttribute($targetDefinition)) {
                return false;
            }

            // If dependent attribute value not matching => not required
            if ($this->getAttribute($targetName) !== $targetValue) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $key
     *
     * @return bool
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function hasAttribute(string $key): bool
    {
        $this->checkAttributeIsAvailable($key);

        return isset($this->attributes[$key]);
    }

    /**
     * @param string $key
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    private function checkAttributeIsAvailable(string $key): void
    {
        if (!$this->isAttributeAvailable($key)) {
            throw new ShortcodeException('Attribute :key is not available for shortcode :tag', [
                ':key' => $key,
                ':tag' => $this->getTagName(),
            ]);
        }
    }

    /**
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function validateAttributes(): void
    {
        foreach ($this->definitions as $name => $definition) {
            // Check for missing attributes for current state
            if (!$this->hasAttribute($name) && $this->isRequiredAttribute($name)) {
                throw new ShortcodeException('Attribute :name is required', [
                    ':name' => $name,
                ]);
            }
        }
    }

    private function getAttributesWithNonDefaultValues(): array
    {
        return array_filter($this->attributes, function ($value, $name) {
            $definition = $this->getAttributeDefinition($name);

            return $value !== $definition->getDefaultValue();
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function hasContent(): bool
    {
        return (bool)$this->content;
    }

    /**
     * Returns true if current tag may have text content between opening and closing markers
     *
     * @return bool
     */
    public function mayHaveContent(): bool
    {
        return $this->entity->mayHaveContent();
    }

    /**
     * @return null|string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getContent(): ?string
    {
        if (!$this->mayHaveContent()) {
            throw new ShortcodeException('Shortcode [:name] can not have content', [
                ':name' => $this->getTagName(),
            ]);
        }

        return $this->content;
    }

    /**
     * @param string $value
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function setContent(string $value): void
    {
        if (!$this->mayHaveContent()) {
            throw new ShortcodeException('Shortcode [:name] can not have content', [
                ':name' => $this->getTagName(),
            ]);
        }

        $this->content = $value;
    }

    /**
     * Returns string representation of current shortcode
     *
     * @return string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function asHtml(): string
    {
        // Use only attributes which values differ from default one
        $attrs = $this->getAttributesWithNonDefaultValues();

        // Generating shortcode tag
        $name = $this->getTagName();
        $node = '['.$name;

        if ($attrs) {
            $this->validateAttributes();

            $node .= \HTML::attributes(array_filter($attrs));
        }

        if ($this->content && $this->mayHaveContent()) {
            $node .= ']'.$this->content.'[/'.$name.']';
        } else {
            $node .= ' /]';
        }

        return $node;
    }

    /**
     * @return \DOMText
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function asDomText(): \DOMText
    {
        return new \DOMText($this->asHtml());
    }
}
