<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\IFace\Url\AbstractConfigBasedUrlParameter;

class ShortcodeUrlParameter extends AbstractConfigBasedUrlParameter
{
    public const OPTION_TAG_NAME    = 'tag_name';
    public const OPTION_IS_STATIC   = 'is_static';
    public const OPTION_IS_EDITABLE = 'is_editable';

    /**
     * Returns key which will be used for storing model in UrlContainer registry.
     *
     * @return string
     */
    public static function getUrlContainerKey(): string
    {
        return ShortcodeInterface::URL_CONTAINER_KEY;
    }

    public function getTagName(): string
    {
        return $this->getOption(self::OPTION_TAG_NAME);
    }

    public function isStatic(): bool
    {
        return (bool)$this->getOption(self::OPTION_IS_STATIC);
    }

    public function isEditable(): bool
    {
        return (bool)$this->getOption(self::OPTION_IS_EDITABLE);
    }
}
