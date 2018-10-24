<?php
namespace BetaKiller\Content;

use BetaKiller\Content\Shortcode\ShortcodeFacade;
use BetaKiller\DI\Container;
use BetaKiller\View\IFaceView;

class TwigExtension extends \Twig_Extension
{
    public function getFilters()
    {
        $shortcodeFacade = Container::getInstance()->get(ShortcodeFacade::class);

        return [

            new \Twig_Filter('shortcodes', function (array $context, string $text) use ($shortcodeFacade) {
                $request = $context[IFaceView::REQUEST_KEY];

                return $shortcodeFacade->process($text, $request, $context);
            }, ['needs_context' => true, 'is_safe' => ['html']]),

        ];
    }
}
