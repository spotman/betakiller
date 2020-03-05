<?php
declare(strict_types=1);

namespace BetaKiller\Content\Shortcode;

use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Widget\WidgetFacade;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Parser\RegularParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Shortcode\ShortcodeInterface as ThunderShortcodeInterface;

class ShortcodeFacade
{
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

    public function process(string $text, ServerRequestInterface $request, array $context): string
    {
        $handlers = new HandlerContainer();

        $handlers->setDefault(function (ThunderShortcodeInterface $s) use ($request, $context) {
            try {
                return $this->render($s->getName(), $s->getParameters(), $request, $context);
            } catch (\Throwable $e) {
                LoggerHelper::logException($this->logger, $e);

                return null;
            }
        });

        return (new Processor(new RegularParser(), $handlers))->process($text);
    }

    /**
     * @param string                                   $tagName
     * @param array                                    $attributes
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param array                                    $context
     *
     * @return string
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function render(
        string $tagName,
        array $attributes,
        ServerRequestInterface $request,
        array $context
    ): string {
        $shortcode = $this->createFromTagName($tagName, $attributes);

        /** @var \BetaKiller\Widget\ShortcodeWidget $widget */
        $widget = $this->widgetFacade->create('Shortcode');

        $widget->setShortcode($shortcode);

        return $this->widgetFacade->render($widget, $request, $context);
    }

    public function stripTags(string $text): string
    {
        $pattern = '/[[\/\!]*?[^\[\]]*?]/';

        return preg_replace($pattern, '', $text);
    }
}
