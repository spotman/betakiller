<?php

declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

use function htmlentities;

abstract readonly class AbstractStringUrlParameter implements StringUrlParameterInterface
{
    use RawUrlParameterTrait;

    /**
     * @inheritDoc
     */
    public static function fromUriValue(string $value): static
    {
        $value = self::removePrefixAndSuffix($value);

        if (empty($value)) {
            throw new UrlParameterException('Incorrect URI value ":value" for :class', [
                ':class' => static::class,
                ':value' => htmlentities($value, \ENT_QUOTES),
            ]);
        }

        static::check($value);

        return static::createInstance($value);
    }

    protected static function createInstance(string $value): static
    {
        return new static($value);
    }

    /**
     * @param string $value
     */
    protected function __construct(private string $value)
    {
    }

    protected static function check(string $value): void
    {
        // No op by default
    }

    /**
     * @inheritDoc
     */
    public function exportUriValue(): string
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
