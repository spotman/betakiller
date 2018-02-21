<?php
namespace BetaKiller\Content\Shortcode\Attribute;


abstract class AbstractRegexAttribute extends AbstractShortcodeAttribute
{
    protected $pattern;

    /**
     * AbstractRegexAttribute constructor.
     *
     * @param string $name
     * @param string $pattern
     */
    public function __construct(string $name, string $pattern)
    {
        $this->pattern = $pattern;

        parent::__construct($name);
    }

    public function isValueAvailable(string $value): bool
    {
        return preg_match($this->pattern, $value) === 1;
    }
}
