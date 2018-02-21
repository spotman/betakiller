<?php
namespace BetaKiller\Content\Shortcode\Attribute;


class StyleAttribute extends TextAttribute
{
    public function __construct()
    {
        parent::__construct('style');
        $this->optional(null);
    }
}
