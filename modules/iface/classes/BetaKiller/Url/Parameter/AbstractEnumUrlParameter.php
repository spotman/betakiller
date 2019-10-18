<?php
declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

abstract class AbstractEnumUrlParameter extends AbstractRawUrlParameter implements EnumUrlParameterInterface
{
    /**
     * @var string
     */
    private $value;

    /**
     * Process uri and set internal state
     *
     * @param string $uriValue
     */
    protected function importUriValue(string $uriValue): void
    {
        if (!\in_array($uriValue, $this->getAllowedValues(), true)) {
            throw new UrlParameterException('Unknown enum value ":val" for parameter ":name"', [
                ':name' => static::getCodename(),
                ':val'  => \htmlentities($uriValue, \ENT_QUOTES),
            ]);
        }

        $this->value = $uriValue;
    }

    /**
     * Returns composed uri for current state
     *
     * @return string
     */
    public function exportUriValue(): string
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getValue(): string
    {
        return $this->value;
    }

    protected function isValue(string $val): bool
    {
        return $this->value === $val;
    }

    abstract protected function getAllowedValues(): array;
}
