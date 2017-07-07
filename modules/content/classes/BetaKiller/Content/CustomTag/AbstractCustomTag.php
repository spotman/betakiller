<?php
namespace BetaKiller\Content\CustomTag;

use BetaKiller\IFace\Url\AbstractConfigBasedUrlParameter;
use BetaKiller\IFace\Url\UrlParameterInterface;

abstract class AbstractCustomTag extends AbstractConfigBasedUrlParameter implements CustomTagInterface
{
    public static function getCodename(): string
    {
        $className = static::class;
        $pos = strrpos($className, '\\');
        $baseName = substr($className, $pos + 1);
        return str_replace(self::CLASS_SUFFIX, '', $baseName);
    }

    /**
     * Returns key which will be used for storing model in UrlContainer registry.
     *
     * @return string
     */
    public static function getUrlContainerKey(): string
    {
        return CustomTagInterface::URL_CONTAINER_KEY;
    }

    /**
     * Returns true if current parameter is the same as provided one
     *
     * @param \BetaKiller\IFace\Url\UrlParameterInterface|\BetaKiller\Content\CustomTag\CustomTagInterface $parameter
     *
     * @return bool
     */
    public function isSameAs(UrlParameterInterface $parameter): bool
    {
        return $this->getTagName() === $parameter->getTagName();
    }
}
