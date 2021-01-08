<?php
declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

abstract class AbstractStringUrlParameter extends AbstractRawUrlParameter
{
    /**
     * @var string|null
     */
    private ?string $value = null;

    /**
     * @inheritDoc
     */
    protected function importUriValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function exportUriValue(): string
    {
        return $this->value ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getValue(): string
    {
        return $this->value ?? '';
    }
}
