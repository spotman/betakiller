<?php
namespace BetaKiller\Content;

class TwigExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [

            new \Twig_Filter('shortcodes', function($text) {
                return Shortcode\ShortcodeFacade::instance()->process($text);
            }, array('is_safe' => array('html'))),

        ];
    }
}
