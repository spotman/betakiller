<?php
namespace BetaKiller\Content\CustomTag;

use BetaKiller\IFace\Url\AbstractConfigBasedUrlParameter;

class CustomTagUrlParameter extends AbstractConfigBasedUrlParameter
{
    /**
     * Returns key which will be used for storing model in UrlContainer registry.
     *
     * @return string
     */
    public static function getUrlContainerKey(): string
    {
        return CustomTagInterface::URL_CONTAINER_KEY;
    }
}
