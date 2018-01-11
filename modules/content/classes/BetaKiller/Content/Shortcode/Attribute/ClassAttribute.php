<?php
namespace BetaKiller\Content\Shortcode\Attribute;


class ClassAttribute extends RegexAttribute
{
    /**
     * ClassAttribute constructor.
     *
     * @param bool|null $isOptional
     */
    public function __construct(?bool $isOptional = null)
    {
        parent::__construct('class', '/[A-Za-z_\-]+/', $isOptional);
    }
}
