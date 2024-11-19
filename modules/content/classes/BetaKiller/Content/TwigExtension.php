<?php
namespace BetaKiller\Content;

use BetaKiller\Content\Shortcode\ShortcodeFacade;
use BetaKiller\View\DefaultIFaceRenderer;
use BetaKiller\View\TemplateContext;

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
                $request = $context[TemplateContext::KEY_REQUEST];

                return $this->facade->process($text, $request, $context);
            }, ['needs_context' => true, 'is_safe' => ['html']]),

        ];
    }
}
