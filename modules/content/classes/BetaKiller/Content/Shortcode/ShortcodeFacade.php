<?php
declare(strict_types=1);

namespace BetaKiller\Content\Shortcode;

use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Widget\WidgetFacade;
use Psr\Log\LoggerInterface;
use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Parser\RegularParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Shortcode\ShortcodeInterface as ThunderShortcodeInterface;

class ShortcodeFacade
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Widget\WidgetFacade
     */
    private $widgetFacade;

    /**
     * @var \BetaKiller\Content\Shortcode\ShortcodeFactory
     */
    private $shortcodeFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * ShortcodeFacade constructor.
     *
     * @param \BetaKiller\Widget\WidgetFacade                $facade
     * @param \BetaKiller\Content\Shortcode\ShortcodeFactory $factory
     * @param \Psr\Log\LoggerInterface                       $logger
     */
    public function __construct(WidgetFacade $facade, ShortcodeFactory $factory, LoggerInterface $logger)
    {
        $this->widgetFacade     = $facade;
        $this->shortcodeFactory = $factory;
        $this->logger           = $logger;
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
     * @param \BetaKiller\Content\Shortcode\ShortcodeEntityInterface $param
     * @param array|null                                             $attributes
     *
     * @return \BetaKiller\Content\Shortcode\ShortcodeInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createFromEntity(ShortcodeEntityInterface $param, ?array $attributes = null): ShortcodeInterface
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
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Factory\FactoryException
     */
    protected function render(string $tagName, ?array $attributes = null): string
    {
        $shortcode = $this->createFromTagName($tagName, $attributes);

        /** @var \BetaKiller\Widget\ShortcodeWidget $widget */
        $widget = $this->widgetFacade->create('Shortcode');

        $widget->setShortcode($shortcode);

        return $this->widgetFacade->render($widget);
    }

    public function stripTags(string $text): string
    {
        $pattern = '/[[\/\!]*?[^\[\]]*?]/';

        return preg_replace($pattern, '', $text);
    }
}
