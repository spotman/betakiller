<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface I18nKeyInterface extends HasI18nKeyNameInterface
{
    /**
     * @return bool
     */
    public function isPlural(): bool;

    /**
     * Returns i18n value for selected language
     *
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return string|null
     */
    public function getI18nValue(LanguageInterface $lang): ?string;

    /**
     * Stores i18n value for selected language
     *
     * @param \BetaKiller\Model\LanguageInterface $lang
     * @param string                              $value
     */
    public function setI18nValue(LanguageInterface $lang, string $value): void;

    /**
     * Returns first not empty i18n value
     *
     * @return string
     */
    public function getAnyI18nValue(): ?string;
}
