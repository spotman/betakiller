<?php
namespace BetaKiller\IFace\Admin\Content\Shortcode\Editor;

use BetaKiller\Content\Shortcode\Editor\ShortcodeEditorFactory;
use BetaKiller\Content\Shortcode\Editor\ShortcodeEditorInterface;
use BetaKiller\Content\Shortcode\ShortcodeEntityInterface;
use BetaKiller\IFace\Admin\Content\AbstractAdminBase;
use BetaKiller\Url\UrlContainerInterface;

abstract class AbstractEditor extends AbstractAdminBase
{
    /**
     * @var \BetaKiller\Content\Shortcode\ShortcodeEntityInterface
     */
    protected $shortcodeEntity;

    /**
     * @var \BetaKiller\Url\UrlContainerInterface
     */
    protected $urlContainer;

    /**
     * @var \BetaKiller\Content\Shortcode\Editor\ShortcodeEditorFactory
     */
    private $editorFactory;

    public function __construct(ShortcodeEditorFactory $factory, UrlContainerInterface $urlContainer)
    {
        parent::__construct();

        $this->editorFactory = $factory;
        $this->urlContainer  = $urlContainer;
    }

    /**
     * @return \BetaKiller\Content\Shortcode\Editor\ShortcodeEditorInterface
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     */
    protected function getShortcodeEditor(): ShortcodeEditorInterface
    {
        $this->shortcodeEntity = $this->urlContainer->getEntity(ShortcodeEntityInterface::URL_CONTAINER_KEY);

        return $this->editorFactory->createFromEntity($this->shortcodeEntity);
    }
}
