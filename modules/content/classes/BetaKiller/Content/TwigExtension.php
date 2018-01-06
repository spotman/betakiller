<?php
namespace BetaKiller\Content;

use BetaKiller\Content\Shortcode\ShortcodeFacade;
use BetaKiller\DI\Container;

class TwigExtension extends \Twig_Extension
{
    public function getFilters()
    {
        $shortcodeFacade = Container::getInstance()->get(ShortcodeFacade::class);

        return [

            new \Twig_Filter('shortcodes', function($text) use ($shortcodeFacade) {
                return $shortcodeFacade->process($text);
            }, array('is_safe' => array('html'))),

        ];
    }
}
