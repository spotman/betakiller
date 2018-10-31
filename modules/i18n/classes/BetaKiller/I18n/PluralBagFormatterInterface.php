<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

interface PluralBagFormatterInterface
{
    /**
     * @param string $packedPluralString
     *
     * @return \BetaKiller\I18n\PluralBagInterface
     */
    public function parse(string $packedPluralString): PluralBagInterface;

    /**
     * @param \BetaKiller\I18n\PluralBagInterface $plural
     *
     * @return string
     */
    public function compile(PluralBagInterface $plural): string;
}
