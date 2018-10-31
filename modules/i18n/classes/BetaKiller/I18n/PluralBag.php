<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

class PluralBag implements PluralBagInterface
{
    /**
     * @var string[]
     */
    private $values;

    /**
     * PluralBag constructor.
     *
     * @param string[] $values
     */
    public function __construct(array $values)
    {
        foreach ($values as $form => $value) {
            $this->setValue($form, $value);
        }
    }

    public function setValue(string $form, string $value): void
    {
        $this->values[$form] = $value;
    }

    public function getValue(string $form): string
    {
        $value = $this->values[$form] ?? null;

        if (!$value) {
            throw new I18nException('Missing form ":form" but these forms exist: ":exist"', [
                ':exist' => \implode('", "', \array_keys($this->values)),
            ]);
        }

        return $value;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->values;
    }
}
