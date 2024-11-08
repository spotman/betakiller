<?php
declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

trait IntegerEnumUrlParameterTrait
{
    use RawUrlParameterTrait;

    public static function fromUriValue(string $value): static
    {
        $value = self::removePrefixAndSuffix($value);

        return self::from((int)$value);
    }

    /**
     * @inheritDoc
     */
    public function exportUriValue(): string
    {
        return self::addPrefixAndSuffix($this->value);
    }

    /**
     * @return mixed
     */
    public function getValue(): int
    {
        return $this->value;
    }
}
