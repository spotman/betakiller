<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

interface PluralBagFactoryInterface
{
    public function create(array $values): PluralBagInterface;
}
