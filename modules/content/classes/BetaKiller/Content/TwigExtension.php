<?php
namespace BetaKiller\Content;

use \BetaKiller\Helper\Base;

class TwigExtension extends \Twig_Extension
{
    use Base;

    public function getFilters()
    {
        return [

            new \Twig_SimpleFilter('custom_tags', function($text) {
                return \CustomTag::instance()->process($text);
            }, array('is_safe' => array('html'))),

            new \Twig_SimpleFilter('shortcodes', function($text) {
                return Shortcode::instance()->process($text);
            }, array('is_safe' => array('html'))),

        ];
    }
}
