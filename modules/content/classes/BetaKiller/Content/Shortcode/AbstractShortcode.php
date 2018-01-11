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

        foreach ($this->getDefinitions() as $item) {
            $this->definitions[$item->getName()] = $item;
        }
    }

    /**
     * @return \BetaKiller\Content\Shortcode\Attribute\ShortcodeAttributeInterface[]
     */
    abstract protected function getDefinitions(): array;

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
        if (!$this->isAttributeAvailable($name)) {
            throw new ShortcodeException('Attribute [:name] is not available for shortcode [:tag]', [
                ':name' => $name,
                ':tag'  => $this->getTagName(),
            ]);
        }

        if (!$this->isAttributeValueAvailable($name, $value)) {
            throw new ShortcodeException('Attribute [:name] value [:value] is not allowed for shortcode [:tag]', [
                ':name'  => $name,
                ':value' => $value,
                ':tag'   => $this->getTagName(),
            ]);
        }

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
    private function isAttributeValueAvailable(string $name, string $value): bool
    {
        $definition = $this->getAttributeDefinition($name);

        return $definition->isValueAvailable($value);
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
     * @param string $key
     *
     * @return null|string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getAttribute(string $key): ?string
    {
        if (!$this->isAttributeAvailable($key)) {
            throw new ShortcodeException('Attribute :key is not available for shortcode :tag', [
                ':key' => $key,
                ':tag' => $this->getTagName(),
            ]);
        }

        return $this->attributes[$key] ?? null;
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
     */
    public function asHtml(): string
    {
        $name  = $this->getTagName();
        $attrs = $this->getAttributes();

        // Generating shortcode tag
        $node = '['.$name;

        if ($attrs) {
            $node .= \HTML::attributes(array_filter($attrs));
        }

        if ($this->content && $this->mayHaveContent()) {
            $node .= ']'.$this->content.'[/'.$name.']';
        } else {
            $node .= ' /]';
        }

        return $node;
    }

    public function asDomText(): \DOMText
    {
        return new \DOMText($this->asHtml());
    }
}
