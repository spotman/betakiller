<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Model\ConfigBasedDispatchableEntityInterface;

interface ShortcodeEntityInterface extends ConfigBasedDispatchableEntityInterface
{
    public const URL_CONTAINER_KEY    = 'Shortcode';
    public const TYPE_STATIC          = 'static';
    public const TYPE_DYNAMIC         = 'dynamic';
    public const TYPE_CONTENT_ELEMENT = 'content-element';

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return bool
     */
    public function isStatic(): bool;

    /**
     * @return bool
     */
    public function isDynamic(): bool;

    /**
     * @return bool
     */
    public function isContentElement(): bool;

    /**
     * @return string
     */
    public function getTagName(): string;

    /**
     * Returns true if current tag may have text content between opening and closing markers
     *
     * @return bool
     */
    public function mayHaveContent(): bool;

    /**
     * Returns true if current tag is editable in WYSIWYG editor
     *
     * @return bool
     */
    public function isEditable(): bool;

    /**
     * @return bool
     */
    public function isDeletable(): bool;
}
