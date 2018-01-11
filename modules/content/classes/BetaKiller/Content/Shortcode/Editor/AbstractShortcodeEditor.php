<?php
namespace BetaKiller\Content\Shortcode\Editor;

use BetaKiller\Content\Shortcode\ShortcodeEntityInterface;

abstract class AbstractShortcodeEditor implements ShortcodeEditorInterface
{
    /**
     * @var ShortcodeEntityInterface
     */
    protected $entity;

    /**
     * AbstractShortcodeEditor constructor.
     *
     * @param \BetaKiller\Content\Shortcode\ShortcodeEntityInterface $entity
     */
    public function __construct(ShortcodeEntityInterface $entity)
    {
        $this->entity = $entity;
    }
}
