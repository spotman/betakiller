<?php
namespace BetaKiller\Content\Shortcode\Attribute;


class RegexAttribute extends AbstractShortcodeAttribute
{
    protected $pattern;

    /**
     * RegexAttribute constructor.
     *
     * @param string    $name
     * @param string    $pattern
     * @param bool|null $isOptional
     */
    public function __construct(string $name, string $pattern, bool $isOptional = null)
    {
        $this->pattern = $pattern;

        parent::__construct($name, $isOptional);
    }

    public function isValueAvailable(string $value): bool
    {
        return preg_match($this->pattern, $value) === 1;
    }
}
