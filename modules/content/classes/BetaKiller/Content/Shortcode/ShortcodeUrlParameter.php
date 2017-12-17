<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\IFace\Url\AbstractConfigBasedUrlParameter;

class ShortcodeUrlParameter extends AbstractConfigBasedUrlParameter
{
    private const OPTION_NAME_IS_STATIC = 'is_static';

    /**
     * Returns key which will be used for storing model in UrlContainer registry.
     *
     * @return string
     */
    public static function getUrlContainerKey(): string
    {
        return ShortcodeInterface::URL_CONTAINER_KEY;
    }

    public function isStatic(): bool
    {
        return (bool)$this->getOption(self::OPTION_NAME_IS_STATIC);
    }
}
