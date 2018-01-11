<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Helper\LoggerHelperTrait;
use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Parser\RegularParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Shortcode\ShortcodeInterface as ThunderShortcodeInterface;


class ShortcodeFacade
{
    use LoggerHelperTrait;

    /**
     * @Inject
     * @var \BetaKiller\Repository\ShortcodeRepository
     */
    private $repository;

    /**
     * @Inject
     * @var \BetaKiller\Widget\WidgetFactory
     */
    private $widgetFactory;

    /**
     * @Inject
     * @var \BetaKiller\Content\Shortcode\ShortcodeFactory
     */
    private $shortcodeFactory;

    /**
     * @Inject
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function getEditableTagsNames(): array
    {
        $output = [];

        foreach ($this->repository->getAll() as $entity) {
            if ($entity->isEditable()) {
                $output[] = $entity->getTagName();
            }
        }

        return $output;
    }

    /**
     * @param string     $tagName
     * @param array|null $attributes
     *
     * @return \BetaKiller\Content\Shortcode\ShortcodeInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function createFromTagName(string $tagName, ?array $attributes = null): ShortcodeInterface
    {
        return $this->shortcodeFactory->createFromTagName($tagName, $attributes);
    }

    /**
     * @param string     $codename
     * @param array|null $attributes
     *
     * @return \BetaKiller\Content\Shortcode\ShortcodeInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createFromCodename(string $codename, ?array $attributes = null): ShortcodeInterface
    {
        return $this->shortcodeFactory->createFromCodename($codename, $attributes);
    }

    /**
     * @param \BetaKiller\Content\Shortcode\ShortcodeEntity $param
     * @param array|null                                    $attributes
     *
     * @return \BetaKiller\Content\Shortcode\ShortcodeInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createFromEntity(ShortcodeEntity $param, ?array $attributes = null): ShortcodeInterface
    {
        return $this->shortcodeFactory->createFromEntity($param, $attributes);
    }

    public function process(string $text): string
    {
        $handlers = new HandlerContainer();

        $handlers->setDefault(function (ThunderShortcodeInterface $s) {
            try {
                return $this->render($s->getName(), $s->getParameters());
            } catch (\Throwable $e) {
                $this->logException($this->logger, $e);

                return null;
            }
        });

        $processor = new Processor(new RegularParser(), $handlers);

        return $processor->process($text);
    }

    /**
     * @param string     $tagName
     * @param array|null $attributes
     *
     * @return string
     * @throws \BetaKiller\Factory\FactoryException
     */
    protected function render(string $tagName, ?array $attributes = null): string
    {
        $shortcode = $this->createFromTagName($tagName, $attributes);

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
