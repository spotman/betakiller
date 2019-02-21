<?php
namespace BetaKiller\Content;

use BetaKiller\Content\Shortcode\ShortcodeFacade;
use BetaKiller\View\IFaceView;

class TwigExtension extends \Twig_Extension
{
    /**
     * @var \BetaKiller\Content\Shortcode\ShortcodeFacade
     */
    private $facade;

    /**
     * TwigExtension constructor.
     *
     * @param \BetaKiller\Content\Shortcode\ShortcodeFacade $facade
     */
    public function __construct(ShortcodeFacade $facade)
    {
        $this->facade = $facade;
    }

    public function getFilters()
    {
        return [

            new \Twig_Filter('shortcodes', function (array $context, string $text) {
                $request = $context[IFaceView::REQUEST_KEY];

                return $this->facade->process($text, $request, $context);
            }, ['needs_context' => true, 'is_safe' => ['html']]),

        ];
    }
}
