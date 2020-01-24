<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Model\AbstractConfigBasedDispatchableEntity;

final class ShortcodeEntity extends AbstractConfigBasedDispatchableEntity implements ShortcodeEntityInterface
{
    /**
     * @return string
     */
    public static function getModelName(): string
    {
        return self::MODEL_NAME;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->getConfigOption(self::OPTION_TYPE);
    }

    /**
     * @return string
     */
    public function getTagName(): string
    {
        return $this->getConfigOption(self::OPTION_TAG_NAME);
    }

    /**
     * @return bool
     */
    public function isStatic(): bool
    {
        return $this->isType(self::TYPE_STATIC);
    }

    /**
     * @return bool
     */
    public function isDynamic(): bool
    {
        return $this->isType(self::TYPE_DYNAMIC);
    }

    /**
     * @return bool
     */
    public function isContentElement(): bool
    {
        return $this->isType(self::TYPE_CONTENT_ELEMENT);
    }

    /**
     * Returns true if current tag may have text content between opening and closing markers
     *
     * @return bool
     */
    public function mayHaveContent(): bool
    {
        return (bool)$this->getConfigOption(self::OPTION_MAY_HAVE_CONTENT, false);
    }

    /**
     * Returns true if current tag is editable in WYSIWYG editor
     *
     * @return bool
     */
    public function isEditable(): bool
    {
        return !$this->isStatic();
    }

    /**
     * @return bool
     */
    public function isDeletable(): bool
    {
        return !$this->isStatic();
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    private function isType(string $type): bool
    {
        return $this->getType() === $type;
    }
}
