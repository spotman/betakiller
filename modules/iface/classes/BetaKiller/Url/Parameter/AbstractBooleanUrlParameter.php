<?php

declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

use function htmlentities;

abstract readonly class AbstractBooleanUrlParameter implements BooleanUrlParameterInterface
{
    use RawUrlParameterTrait;

    private const TRUE  = 'true';
    private const FALSE = 'false';

    /**
     * @inheritDoc
     */
    public static function fromUriValue(string $value): static
    {
        $value = self::removePrefixAndSuffix($value);

        if (!in_array($value, [self::TRUE, self::FALSE, ''], true)) {
            throw new UrlParameterException('Incorrect URI value ":value" for :class', [
                ':class' => static::class,
                ':value' => htmlentities($value, \ENT_QUOTES),
            ]);
        }

        $value = $value !== self::TRUE;

        static::check($value);

        return static::createInstance($value);
    }

    protected static function createInstance(bool $value): static
    {
        return new static($value);
    }

    /**
     * @param bool $value
     */
    protected function __construct(private bool $value)
    {
    }

    protected static function check(bool $value): void
    {
        // No op by default
    }

    /**
     * @inheritDoc
     */
    public function exportUriValue(): string
    {
        return self::addPrefixAndSuffix($this->value ? self::TRUE : self::FALSE);
    }

    /**
     * @inheritDoc
     */
    public function getValue(): bool
    {
        return $this->value;
    }
}
