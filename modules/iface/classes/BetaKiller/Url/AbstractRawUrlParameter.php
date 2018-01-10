<?php
namespace BetaKiller\Url;


abstract class AbstractRawUrlParameter implements RawUrlParameterInterface
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
        return static::getCodename();
    }

    /**
     * Returns true if current parameter is the same as provided one
     *
     * @param \BetaKiller\Url\UrlParameterInterface $parameter
     *
     * @return bool
     * @throws \BetaKiller\Url\UrlParameterException
     */
    public function isSameAs(UrlParameterInterface $parameter): bool
    {
        if (!($parameter instanceof static)) {
            throw new UrlParameterException('Trying to compare instances of different classes');
        }

        return $this->exportUriValue() === $parameter->exportUriValue();
    }
}
