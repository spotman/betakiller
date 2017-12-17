<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Utils\Instance\SingletonTrait;
use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Parser\RegularParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Shortcode\ShortcodeInterface as ThunderShortcodeInterface;


class ShortcodeFacade
{
    use SingletonTrait;

    /**
     * @Inject
     * @var \BetaKiller\Repository\ShortcodeRepository
     */
    private $repository;

    /**
     * @Inject
     * @var \BetaKiller\IFace\WidgetFactory
     */
    private $widgetFactory;

    /**
     * @Inject
     * @var \BetaKiller\Content\Shortcode\ShortcodeFactory
     */
    private $shortcodeFactory;

    public function getEditableTagsNames(): array
    {
        foreach ($this->repository->getAll() as $item) {
            $item->getCodename();
        }

        // TODO Decide where to store "is_editable" flag: on config or in class (config is better coz there may be no class at all)

        return [];
    }

    public function create(string $tagName, ?array $attributes = null): ShortcodeInterface
    {
        $tagCodename = $this->shortcodeFactory->convertTagNameToCodename($tagName);

        $urlParameter = $this->repository->findByCodename($tagCodename);

        // Use common class for static shortcodes
        $classCodename = $urlParameter->isStatic()
            ? StaticShortcode::codename()
            : $urlParameter->getCodename();

        return $this->shortcodeFactory->create($tagName, $attributes, $classCodename);
    }


    public function process(string $text): string
    {
        $handlers = new HandlerContainer();

        $handlers->setDefault(function (ThunderShortcodeInterface $s) {
            return $this->render($s->getName(), $s->getParameters());
        });

        $processor = new Processor(new RegularParser(), $handlers);

        return $processor->process($text);
    }

    protected function render(string $tagName, ?array $attributes = null): string
    {
        $shortcode = $this->create($tagName, $attributes);

        /** @var \BetaKiller\Widget\ShortcodeWidget $widget */
        $widget = $this->widgetFactory->create('Shortcode');

        $widget->setShortcode($shortcode);

        return $widget->render();
    }

    public function stripTags(string $text): string
    {
        $pattern = '/[[\/\!]*?[^\[\]]*?]/';

        return preg_replace($pattern, '', $text);
    }
}
