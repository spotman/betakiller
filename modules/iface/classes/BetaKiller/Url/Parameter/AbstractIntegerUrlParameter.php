<?php

declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

abstract readonly class AbstractIntegerUrlParameter implements IntegerUrlParameterInterface
{
    use RawUrlParameterTrait;

    /**
     * @inheritDoc
     */
    public static function fromUriValue(string $value): static
    {
        $value = self::removePrefixAndSuffix($value);

        if (!is_numeric($value)) {
            throw new UrlParameterException('Incorrect URI value ":value" for :class', [
                ':class' => static::class,
                ':value' => htmlentities($value, \ENT_QUOTES),
            ]);
        }

        $value = (int)$value;

        static::check($value);

        return static::create($value);
    }

    public static function create(int $value): static
    {
        return new static($value);
    }

    /**
     * @param int $value
     */
    protected function __construct(private int $value)
    {
    }

    protected static function check(int $value): void
    {
        // No op by default
    }

    /**
     * @inheritDoc
     */
    public function exportUriValue(): string
    {
        return self::addPrefixAndSuffix($this->value);
    }

    /**
     * @inheritDoc
     */
    public function getValue(): int
    {
        return $this->value;
    }
}
