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
     * @return array
     */
    public function getAll(): array;
}
