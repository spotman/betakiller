<?php
declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

abstract class AbstractRawUrlParameter implements RawUrlParameterInterface
{
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
     * AbstractRawUrlParameter constructor.
     *
     * @param string $value
     */
    public function __construct(string $value = null)
    {
        if ($value !== null) {
            $this->importUriValue($value);
        }
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

    protected static function getCodename(): string
    {
        $className = static::class;
        $pos       = strrpos($className, '\\');
        $baseName  = substr($className, $pos + 1);

        return str_replace(self::CLASS_SUFFIX, '', $baseName);
    }

    /**
     * Process uri and set internal state
     *
     * @param string $value
     */
    abstract protected function importUriValue(string $value): void;

    /**
     * @inheritDoc
     */
    public function isCachingAllowed(): bool
    {
        // Caching for raw parameters is allowed by default
        return true;
    }
}
