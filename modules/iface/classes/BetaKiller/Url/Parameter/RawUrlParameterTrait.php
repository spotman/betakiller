<?php

declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

trait RawUrlParameterTrait
{
    /**
     * Returns key which will be used for storing model in UrlContainer registry.
     *
     * @return string
     */
    public static function getUrlContainerKey(): string
    {
        return static::getParameterCodename();
    }

    /**
     * Returns true if current parameter is the same as provided one
     *
     * @param \BetaKiller\Url\Parameter\UrlParameterInterface $parameter
     *
     * @return bool
     * @throws \BetaKiller\Url\Parameter\UrlParameterException
     */
    public function isSameAs(UrlParameterInterface $parameter): bool
    {
        if (!($parameter instanceof static)) {
            throw new UrlParameterException('Trying to compare instances of different classes');
        }

        return $this->exportUriValue() === $parameter->exportUriValue();
    }

    /**
     * @inheritDoc
     */
    public function isCachingAllowed(): bool
    {
        // Caching for raw parameters is allowed by default
        return true;
    }

    final protected static function getParameterCodename(): string
    {
        $className = static::class;
        $pos       = strrpos($className, '\\');
        $baseName  = substr($className, $pos + 1);

        return str_replace(RawUrlParameterInterface::CLASS_SUFFIX, '', $baseName);
    }

    protected static function getUriPrefix(): string
    {
        return '';
    }

    protected static function getUriSuffix(): string
    {
        return '';
    }

    protected static function getUriDelimiter(): string
    {
        return '-';
    }

    protected static function addPrefixAndSuffix(string|int $rawValue): string
    {
        $prefix = self::getUriPrefix();
        $suffix = self::getUriSuffix();
        $delim  = self::getUriDelimiter();

        if ($prefix !== '') {
            $rawValue = $prefix.$delim.$rawValue;
        }

        if ($suffix !== '') {
            $rawValue = $rawValue.$delim.$suffix;
        }

        return $rawValue;
    }

    protected static function removePrefixAndSuffix(string $uriValue): string
    {
        $prefix = self::getUriPrefix();
        $suffix = self::getUriSuffix();
        $delim  = self::getUriDelimiter();

        if ($prefix !== '') {
            $uriValue = str_replace($prefix.$delim, '', $uriValue);
        }

        if ($suffix !== '') {
            $uriValue = str_replace($delim.$suffix, '', $uriValue);
        }

        return $uriValue;
    }
}
