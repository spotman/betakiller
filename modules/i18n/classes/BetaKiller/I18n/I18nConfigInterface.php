<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

interface I18nConfigInterface
{
    /**
     * @return string[]
     */
    public function getAllowedLanguages(): array;

    /**
     * @return string|null
     */
    public function getFallbackLanguage(): ?string;

    /**
     * Returns class names of loaders
     *
     * @return string[]
     */
    public function getLoaders(): array;

    /**
     * @return string[]
     * @throws \BetaKiller\Exception
     */
    public function getFallbackOnlyKeys(): array;
}
