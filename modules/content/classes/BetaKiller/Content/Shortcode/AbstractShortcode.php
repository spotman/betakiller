<?php
namespace BetaKiller\Content\Shortcode;

abstract class AbstractShortcode implements ShortcodeInterface
{
    /**
     * @var string
     */
    private $tagName;

    /**
     * @var string
     */
    private $codename;

    /**
     * @var array Shortcode tag values
     */
    private $attributes = [];

    /**
     * @var string
     */
    private $content;

    public static function codename(): string
    {
        $className = static::class;
        $pos       = strrpos($className, '\\');
        $baseName  = substr($className, $pos + 1);

        return str_replace(self::CLASS_SUFFIX, '', $baseName);
    }

    public function __construct(string $tagName, ?string $codename = null)
    {
        $this->tagName = $tagName;
        $this->codename = $codename ?? static::codename();
    }

    /**
     * Returns HTML tag name
     *
     * @return string
     */
    public function getTagName(): string
    {
        return $this->tagName;
    }

    public function getCodename(): string
    {
        return $this->codename;
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
     * @param string      $key
     * @param null|string $value
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function setAttribute(string $key, ?string $value): void
    {
        if (!$this->isAttributeAvailable($key)) {
            throw new ShortcodeException('Attribute :key is not available for shortcode :tag', [
                ':key' => $key,
                ':tag' => $this->getTagName(),
            ]);
        }

        // TODO Check for available values for attribute
        $this->attributes[$key] = $value;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    private function isAttributeAvailable(string $key): bool
    {
        // TODO Check is attribute available for current tag

        return (bool)$key;
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
        $name    = $this->getTagName();
        $attrs   = $this->getAttributes();

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
