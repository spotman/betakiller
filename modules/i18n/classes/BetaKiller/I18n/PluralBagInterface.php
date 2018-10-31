<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

interface PluralBagInterface
{
    /**
     * @param string $form
     *
     * @return string
     */
    public function getValue(string $form): string;

    /**
     * @param string $form
     * @param string $value
     */
    public function setValue(string $form, string $value): void;

    /**
     * @return array
     */
    public function getAll(): array;
}
