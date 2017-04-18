<?php
namespace BetaKiller\Content;

class TwigExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [

            new \Twig_SimpleFilter('custom_tags', function($text) {
                return \CustomTag::instance()->process($text);
            }, array('is_safe' => array('html'))),

            new \Twig_SimpleFilter('shortcodes', function($text) {
                return Shortcode::getInstance()->process($text);
            }, array('is_safe' => array('html'))),

        ];
    }
}
