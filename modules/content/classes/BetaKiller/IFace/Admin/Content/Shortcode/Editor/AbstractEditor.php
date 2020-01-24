<?php
namespace BetaKiller\IFace\Admin\Content\Shortcode\Editor;

use BetaKiller\Content\Shortcode\Editor\ShortcodeEditorFactory;
use BetaKiller\Content\Shortcode\Editor\ShortcodeEditorInterface;
use BetaKiller\Content\Shortcode\ShortcodeEntityInterface;
use BetaKiller\Content\Shortcode\ShortcodeFacade;
use BetaKiller\IFace\Admin\Content\AbstractContentAdminIFace;
use BetaKiller\Url\Container\UrlContainerInterface;

abstract class AbstractEditor extends AbstractContentAdminIFace
{
    /**
     * @var \BetaKiller\Content\Shortcode\ShortcodeEntityInterface
     */
    protected $shortcodeEntity;

    /**
     * @var \BetaKiller\Url\Container\UrlContainerInterface
     */
    protected $urlContainer;

    /**
     * @var \BetaKiller\Content\Shortcode\ShortcodeFacade
     */
    protected $shortcodeFacade;

    /**
     * @var \BetaKiller\Content\Shortcode\Editor\ShortcodeEditorFactory
     */
    private $editorFactory;

    public function __construct(
        ShortcodeEditorFactory $factory,
        UrlContainerInterface $urlContainer,
        ShortcodeFacade $facade
    ) {
        $this->editorFactory = $factory;
        $this->urlContainer = $urlContainer;
        $this->shortcodeFacade = $facade;
    }

    /**
     * @return \BetaKiller\Content\Shortcode\Editor\ShortcodeEditorInterface
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     */
    protected function getShortcodeEditor(): ShortcodeEditorInterface
    {
        $this->shortcodeEntity = $this->urlContainer->getEntity(ShortcodeEntityInterface::MODEL_NAME);

        return $this->editorFactory->createFromEntity($this->shortcodeEntity);
    }
}
