<?php
namespace BetaKiller\Content\Shortcode\Editor;

use BetaKiller\Content\Shortcode\ShortcodeEntityInterface;
use BetaKiller\Content\Shortcode\ShortcodeFacade;

abstract class AbstractShortcodeEditor implements ShortcodeEditorInterface
{
    /**
     * @var ShortcodeEntityInterface
     */
    protected $shortcodeEntity;

    /**
     * @var \BetaKiller\Content\Shortcode\ShortcodeFacade
     */
    protected $shortcodeFacade;

    /**
     * AbstractShortcodeEditor constructor.
     *
     * @param \BetaKiller\Content\Shortcode\ShortcodeEntityInterface $entity
     * @param \BetaKiller\Content\Shortcode\ShortcodeFacade          $facade
     */
    public function __construct(ShortcodeEntityInterface $entity, ShortcodeFacade $facade)
    {
        $this->shortcodeEntity = $entity;
        $this->shortcodeFacade = $facade;
    }
}
