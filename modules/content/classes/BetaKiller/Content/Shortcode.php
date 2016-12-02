<?php
namespace BetaKiller\Content;

use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Parser\RegularParser;
use BetaKiller\IFace\WidgetFactory;

class Shortcode
{
    use \BetaKiller\Utils\Instance\Cached;

    /**
     * Shortcode constructor.
     */
    protected function __construct() {}

    public function process($text)
    {
        $handlers = new HandlerContainer();

        $handlers->setDefault(function(ShortcodeInterface $s) {
            return $this->render_shortcode($s->getName());
        });

        $processor = new Processor(new RegularParser(), $handlers);

        return $processor->process($text);
    }

    protected function render_shortcode($name)
    {
        $name = 'Shortcode_'.str_replace('-', '_', $name);

        // Make every word uppercase (like in other widgets)
        $name = implode('_', array_map('ucfirst', explode('_', $name)));

        $widget = WidgetFactory::instance()->create($name);
        return $widget->render();
    }
}
