<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

interface LoaderInterface
{
    /**
     * Returns "key" => "translated string" pairs for provided locale
     *
     * @param string $locale
     *
     * @return string[]
     */
    public function load(string $locale): array;
}
