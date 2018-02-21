<?php
namespace BetaKiller\Content\Shortcode\Attribute;


class ClassAttribute extends RegexAttribute
{
    /**
     * ClassAttribute constructor.
     */
    public function __construct()
    {
        parent::__construct('class', '/[A-Za-z_\-]+/');
    }
}
