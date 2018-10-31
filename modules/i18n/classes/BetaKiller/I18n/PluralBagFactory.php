<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

class PluralBagFactory implements PluralBagFactoryInterface
{
    public function create(array $values): PluralBagInterface
    {
        return new PluralBag($values);
    }
}
